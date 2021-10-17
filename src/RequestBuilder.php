<?php

declare(strict_types=1);

namespace RestClient;

use ReflectionClass;
use RestClient\Attribute\ApiEndpoint;
use RestClient\Attribute\Cache;
use RestClient\Attribute\Handler;
use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use RestClient\Attribute\Url;
use RestClient\Authentication\BasicAuthenticator;
use RestClient\Authentication\TokenAuthenticator;
use RestClient\Dto\Request;
use RestClient\Exceptions\ConstraintViolation;
use RestClient\Exceptions\MissingParameter;
use RestClient\Exceptions\OverrideExistingParameter;
use RestClient\Exceptions\WrongParameter;
use RestClient\Interfaces\Authenticator;
use RestClient\Interfaces\RequestBuilderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraints\Url as ConstraintsUrl;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBuilder implements RequestBuilderInterface
{
    private Authenticator $authentication;
    private object $entity;
    private array $headers = [];
    private array $query = [];
    private array $json = [];

    private ?Request $request = null;

    private ReflectionClass $reflectEntity;

    private array $possibleHttpMethods = [];
    private array $possibleTypes = [];
    private ValidatorInterface|RecursiveValidator $validator;
    private int $cacheExpiresAfter;
    private float $cacheBeta;

    public function __construct(private ParameterBagInterface $parameterBag)
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $this->possibleTypes = (new \ReflectionClass(Type::class))->getConstants();
        $this->possibleHttpMethods = (new \ReflectionClass(HttpMethod::class))->getConstants();
    }

    /**
     * Only if you dont use Attribute on the Entity.
     */
    public function setAuthentication(Authenticator $authentication): self
    {
        $this->authentication = $authentication;

        return $this;
    }

    public function setEntity(object $entity): self
    {
        $this->reset();
        $this->entity = $entity;
        $this->reflectEntity = new ReflectionClass(\get_class($entity));

        return $this;
    }

    public function setCacheBeta(float $cacheBeta): RequestBuilderInterface
    {
        $this->cacheBeta = $cacheBeta;
        return $this;
    }

    public function setCacheExpiresAfter(int $cacheExpiresAfter): RequestBuilderInterface
    {
        $this->cacheExpiresAfter = $cacheExpiresAfter;
        return $this;
    }

    private function getCacheExpiresAfter(): ?int
    {
        if (isset($this->cacheExpiresAfter)) {
            return $this->cacheExpiresAfter;
        }

        $attributes = $this->reflectEntity->getAttributes(Cache::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (count($attributes) === 1) {
            $value = $attributes[0]->newInstance()->getCacheExpiresAfter();
            if ($value !== null) {
                return $value;
            }
        }
        if ($this->cacheIsSet()) {
            return $this->parameterBag->get('rest_client.cache')["expiresAfter"];
        }
        return null;
    }

    private function getCacheBeta(): ?float
    {

        if (isset($this->cacheBeta)) {
            return $this->cacheBeta;
        }

        $attributes = $this->reflectEntity->getAttributes(Cache::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) === 1) {
            $value = $attributes[0]->newInstance()->getCacheBeta();

            if ($value !== null) {
                return $value;
            }
        }

        if ($this->cacheIsSet()) {
            return $this->parameterBag->get('rest_client.cache')["beta"];
        }
        return null;
    }

    private function cacheIsSet(): bool
    {
        return count($this->reflectEntity->getAttributes(Cache::class, \ReflectionAttribute::IS_INSTANCEOF)) === 1;
    }

    /**
     * @throws OverrideExistingParameter
     */
    public function addHeader(string $fieldName, mixed $fieldValue, bool $exception = true): self
    {
        if (isset($this->headers[$fieldName]) && $exception) {
            throw new OverrideExistingParameter(sprintf('You can not override the %s Value', $fieldName));
        }
        $this->headers[$fieldName] = $fieldValue;

        return $this;
    }

    /**
     * @throws MissingParameter
     * @throws WrongParameter
     * @throws OverrideExistingParameter
     * @throws ConstraintViolation
     */
    public function getRequest(): Request
    {
        if ($this->request instanceof Request) {
            return $this->request;
        }
        $request = (new Request())
            ->setHttpMethod($this->getHttpMethod())
            ->setUrl($this->getUrl())
            ->setCacheExpiresAfter($this->getCacheExpiresAfter())
            ->setCacheBeta($this->getCacheBeta())
            ->setId($this->newRequestId());

        $handler = $this->getHandlerFromHandlerAttribute();
        if ($handler !== null and $handler->getClientHandler() !== null) {
            $request->setClientHandler($handler->getClientHandler());
        }
        if ($handler !== null and $handler->getInformationalHandler() !== null) {
            $request->setInformationalHandler($handler->getInformationalHandler());
        }
        if ($handler !== null and $handler->getServerHandler() !== null) {
            $request->setServerHandler($handler->getServerHandler());
        }
        if ($handler !== null and $handler->getSuccessHandler() !== null) {
            $request->setSuccessHandler($handler->getSuccessHandler());
        }
        if ($handler !== null and $handler->getRedirectionHandler() !== null) {
            $request->setRedirectionHandler($handler->getRedirectionHandler());
        }

        if ('http-basic' === $this->getAuthentication()->getAuthenticationMethod()) {
            $request->setAuthBasic($this->getAuthentication()->getCredentials());
        }
        if ('token' === $this->getAuthentication()->getAuthenticationMethod()) {
            $request->addHeaders($this->getAuthentication()->getCredentials()[0], $this->getAuthentication()->getCredentials()[1]);
        }

        $this->hydrateWithEntity();

        foreach ($this->query as $key => $value) {
            $request->addQuery($key, $value);
        }

        foreach ($this->headers as $key => $value) {
            $request->addHeaders($key, $value);
        }

        foreach ($this->json as $key => $value) {
            $request->addJson($key, $value);
        }

        $this->request = $request;
        return $request;
    }

    /**
     * @throws MissingParameter
     */
    private function getAuthentication(): Authenticator
    {
        $auth = null;
        if ($this->getAuthenticationFromApiAttribute() !== null) {
            $auth = $this->getAuthenticationFromApiAttribute();
        }
        if ($this->getAuthenticationFromAuthenticatorAttribute() !== null) {
            $auth = $this->getAuthenticationFromAuthenticatorAttribute();
        }

        if (isset($this->authentication)) {
            $auth = $this->authentication;
        }

        if (null === $auth) {
            throw new MissingParameter('It must be set a Authenticator.');
        }

        return $auth;
    }

    private function getAuthenticationFromAuthenticatorAttribute(): ?Authenticator
    {
        $attributes = $this->reflectEntity->getAttributes(Authenticator::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (1 !== \count($attributes)) {
            return null;
        }
        return $attributes[0]->newInstance();
    }

    private function getHandlerFromHandlerAttribute(): ?Handler
    {
        $attributes = $this->reflectEntity->getAttributes(Handler::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (1 !== \count($attributes)) {
            return null;
        }
        return $attributes[0]->newInstance();
    }

    /**
     * @throws WrongParameter
     * @throws MissingParameter
     */
    private function getAuthenticationFromApiAttribute(): ?Authenticator
    {
        $attributes = $this->reflectEntity->getAttributes(ApiEndpoint::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (1 !== \count($attributes)) {
            return null;
        }

        $api = $this->getConnection($attributes[0]->newInstance()->getApiEndpoint());

        if (isset($api["username"]) and isset($api["keyField"])) {
            throw new WrongParameter('You cant set username and keyField');
        }
        if (isset($api["username"]) and isset($api["password"])) {
            return (new BasicAuthenticator($api["username"], $api["password"]));
        }
        if (isset($api["keyField"]) and isset($api["keyValue"])) {
            return (new TokenAuthenticator($api["keyField"], $api["keyValue"]));
        }
        return null;
    }

    /**
     * @throws MissingParameter
     * @throws ConstraintViolation
     */
    private function validateEntity(): void
    {
        if (!isset($this->entity)) {
            throw new MissingParameter('It must be set an Entity.');
        }

        $violations = $this->validator->validate($this->entity);

        if (\count($violations) > 0) {
            throw new ConstraintViolation(sprintf('Problems have surfaced with the entity %s', \get_class($this->entity)), $violations);
        }
    }

    /**
     * @throws WrongParameter
     * @throws MissingParameter
     */
    private function getHttpMethod(): string
    {
        $method = null;
        if ($this->getHttpMethodFromHttpAttribute() !== null) {
            $method = $this->getHttpMethodFromHttpAttribute();
        }

        if (is_null($method)) {
            throw new MissingParameter('A Http Method must be set.');
        }

        if (!\in_array($method, $this->possibleHttpMethods, true)) {
            throw new WrongParameter('The HTTP Method must be one of ' . implode(',', $this->possibleHttpMethods));
        }

        return $method;
    }

    private function getHttpMethodFromHttpAttribute(): ?string
    {
        $attributes = $this->reflectEntity->getAttributes(HttpMethod::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (!\is_array($attributes) || \count($attributes) < 1) {
            return null;
        }

        return $attributes[0]->newInstance()->getMethod();
    }

    /**
     * @throws WrongParameter
     * @throws MissingParameter
     * @throws ConstraintViolation
     */
    private function getValuesFromEntity(): array
    {
        $values = [];
        $this->validateEntity();
        $properties = $this->reflectEntity->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $getMethod = 'get' . $propertyName;

            try {
                $propertyValue = $this->entity->{$getMethod}();
            } catch (\Throwable $th) {
                continue;
            }

            $attributes = $property->getAttributes(Type::class, \ReflectionAttribute::IS_INSTANCEOF);
            if (!\is_array($attributes) || \count($attributes) < 1) {
                continue;
            }

            $type = $attributes[0]->newInstance()->getType();
            if (!\in_array($type, $this->possibleTypes, true)) {
                throw new WrongParameter('The Type must be one of ' . implode(',', $this->possibleTypes));
            }

            $values[$type][$propertyName] = $propertyValue;
        }

        return $values;
    }

    /**
     * @throws OverrideExistingParameter
     */
    private function addQuery(string $fieldName, mixed $fieldValue, bool $exception = true): void
    {
        if (isset($this->query[$fieldName]) && $exception) {
            throw new OverrideExistingParameter(sprintf('You can not override the %s Value', $fieldName));
        }
        $this->query[$fieldName] = $fieldValue;
    }

    /**
     * @throws OverrideExistingParameter
     */
    private function addJson(string $fieldName, mixed $fieldValue, bool $exception = true): void
    {
        if (isset($this->json[$fieldName]) && $exception) {
            throw new OverrideExistingParameter(sprintf('You can not override the %s Value', $fieldName));
        }
        $this->json[$fieldName] = $fieldValue;
    }

    /**
     * @throws MissingParameter
     * @throws ConstraintViolation
     */
    private function getUrl(): string
    {
        $url = "";
        if ($this->getUrlSuffixFromUrlAttribute() === null) {
            throw new MissingParameter('A UrlSuffix must be set in the URL Attribute.');
        }
        if ($this->getBaseUrlFromApiAttribute() === null) {
            throw new MissingParameter('A BaseUrl must be set.');
        }

        $url = $this->getBaseUrlFromApiAttribute() . $this->getUrlSuffixFromUrlAttribute();
        $violations = $this->validator->validate($url, new ConstraintsUrl([
            'protocols' => ['http', 'https'],
        ]));

        if (\count($violations) > 0) {
            throw new ConstraintViolation(sprintf('Problems with the Url %s', $url), $violations);
        }

        return $url;
    }

    private function getUrlSuffixFromUrlAttribute(): ?string
    {
        $attributes = $this->reflectEntity->getAttributes(Url::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (!\is_array($attributes) || \count($attributes) < 1) {
            return null;
        }

        return $this->hydrateUrlWithEntity($attributes[0]->newInstance()->getUrl());
    }


    private function hydrateUrlWithEntity(string $url): string
    {
        if (isset($this->getValuesFromEntity()[Type::URLREPLACE]) and is_array($this->getValuesFromEntity()[Type::URLREPLACE])) {
            foreach ($this->getValuesFromEntity()[Type::URLREPLACE] as $key => $value) {
                $url = str_replace('{' . $key . '}', $value, $url);
            }
        }

        return $url;
    }

    private function getBaseUrlFromApiAttribute(): ?string
    {
        $attributes = $this->reflectEntity->getAttributes(ApiEndpoint::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (!\is_array($attributes) || \count($attributes) < 1) {
            return null;
        }

        return $this->getConnection($attributes[0]->newInstance()->getApiEndpoint())["baseurl"];
    }

    /**
     * @throws MissingParameter
     */
    private function getConnection(string $connectionName): array
    {
        if (isset($this->parameterBag->get("rest_client.connections")[$connectionName])) {
            return $this->parameterBag->get("rest_client.connections")[$connectionName];
        }

        throw new MissingParameter(sprintf('The connection %s dont exist', $connectionName));
    }

    /**
     * @throws OverrideExistingParameter
     * @throws WrongParameter
     * @throws ConstraintViolation
     * @throws MissingParameter
     */
    private function hydrateWithEntity(): void
    {
        foreach ($this->getValuesFromEntity() as $type => $items) {
            foreach ($items as $key => $item) {
                if (Type::HEADER == $type) {
                    $this->addHeader($key, $item);
                }
                if (Type::JSON == $type) {
                    $this->addJson($key, $item);
                }
                if (Type::QUERY == $type) {
                    $this->addQuery($key, $item);
                }
            }
        }
    }

    //falls doch wiederholung geben sollte, noch mehr parameter zum hashen dazu nehmen
    private function newRequestId(): string
    {
        $query = $this->getValuesFromEntity()[Type::QUERY] ?? [];
        return sha1($this->getHttpMethod() . $this->getUrl() . implode(",", $query));
    }

    private function reset(): void
    {
        unset($this->authentication);
        unset($this->url);
        unset($this->method);
        unset($this->entity);

        $this->headers = [];
        $this->query = [];
        $this->json = [];

        unset($this->reflectEntity);

        unset($this->cacheExpiresAfter);
        unset($this->cacheBeta);

        $this->request = null;
    }
}
