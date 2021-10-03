<?php

namespace RestClient;


use ReflectionClass;
use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use RestClient\Attribute\Url;
use RestClient\Dto\Request;
use RestClient\Exceptions\ConstraintViolation;
use RestClient\Exceptions\MissingParameter;
use RestClient\Exceptions\OverrideExistingParameter;
use RestClient\Exceptions\WrongParameter;
use RestClient\Interfaces\Authenticator;
use Symfony\Component\Validator\Validation;
use \Symfony\Component\Validator\Constraints\Url as ConstraintsUrl;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBuilder
{
    private Authenticator $authentication;
    private Object $entity;
    private array $headers = [];
    private array $query = [];
    private array $json = [];

    private ReflectionClass $reflectEntity;

    private array $possibleHttpMethods = [];
    private array $possibleTypes = [];
    private ValidatorInterface|RecursiveValidator $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $this->possibleTypes= (new \ReflectionClass(Type::class))->getConstants();
        $this->possibleHttpMethods = (new \ReflectionClass(HttpMethod::class))->getConstants();
    }

    /**
     * Only if you dont use Attribute on the Entity
     */
    public function setAuthentication(Authenticator $authentication):self
    {
        $this->authentication = $authentication;

        return $this;
    }

    /**
     * @throws MissingParameter
     */
    private function getAuthentication(): Authenticator
    {
        $auth = null;
        $attributes = $this->reflectEntity->getAttributes(Authenticator::class, \ReflectionAttribute::IS_INSTANCEOF);

        if(is_array($attributes) AND count($attributes) === 1){
            $auth = $attributes[0]->newInstance();
        }

        if(isset($this->authentication)){
            $auth = $this->authentication;
        }

        if($auth === null){
            throw new MissingParameter('It must be set a Authenticator.');
        }

        return $auth;
    }

    /**
     * @throws MissingParameter
     * @throws ConstraintViolation
     */
    private function validateEntity():void
    {
        if(!isset($this->entity)){
            throw new MissingParameter('It must be set an Entity.');
        }

        $violations = $this->validator->validate($this->entity);

        if(count($violations)>0){
            throw new ConstraintViolation(sprintf('Problems have surfaced with the entity %s',get_class($this->entity)),$violations);
        }
    }

    public function setEntity(Object $entity): RequestBuilder
    {
        $this->entity = $entity;
        $this->reflectEntity = new ReflectionClass(get_class($entity));

        return $this;
    }

    /**
     * @throws WrongParameter
     * @throws MissingParameter
     */
    private function getHttpMethod(): string
    {
        $attributes = $this->reflectEntity->getAttributes(HttpMethod::class, \ReflectionAttribute::IS_INSTANCEOF);

        if(!is_array($attributes) OR count($attributes)<1){
            throw new MissingParameter('A Http Method must be set.');
        }

        $method = $attributes[0]->newInstance()->getMethod();

        if(!in_array($method,$this->possibleHttpMethods)){
            throw new WrongParameter('The HTTP Method must be one of '.implode(',',$this->possibleHttpMethods));
        }

        return $method;
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

        foreach ($properties as $property){
            $propertyName = $property->getName();
            $getMethod = "get".$propertyName;
            try {
                $propertyValue = $this->entity->$getMethod();
            }catch (\Throwable $th){
                continue;
            }

            $attributes = $property->getAttributes(Type::class, \ReflectionAttribute::IS_INSTANCEOF);
            if(!is_array($attributes) OR count($attributes)<1){
                continue;
            }

            $type = $attributes[0]->newInstance()->getType();
            if(!in_array($type,$this->possibleTypes)){
                throw new WrongParameter('The Type must be one of '.implode(',',$this->possibleTypes));
            }

            $values[$type][$propertyName] = $propertyValue;
        }

        return $values;
    }

    /**
     * @throws OverrideExistingParameter
     */
    public function addHeader(string $fieldName, string $fieldValue, bool $exception = true): self
    {
        if(isset($this->headers[$fieldName]) AND $exception){
            throw new OverrideExistingParameter(sprintf('You can not override the %s Value',$fieldName));
        }
        $this->headers[$fieldName] = $fieldValue;

        return $this;
    }

    /**
     * @throws OverrideExistingParameter
     */
    private function addQuery(string $fieldName, string $fieldValue, bool $exception = true){
        if(isset($this->query[$fieldName]) AND $exception){
            throw new OverrideExistingParameter(sprintf('You can not override the %s Value',$fieldName));
        }
        $this->query[$fieldName] = $fieldValue;
    }


    /**
     * @throws OverrideExistingParameter
     */
    private function addJson(string $fieldName, string $fieldValue, bool $exception = true){
        if(isset($this->json[$fieldName]) AND $exception){
            throw new OverrideExistingParameter(sprintf('You can not override the %s Value',$fieldName));
        }
        $this->json[$fieldName] = $fieldValue;
    }


    /**
     * @throws MissingParameter
     * @throws ConstraintViolation
     */
    private function getUrl():string
    {
        $attributes = $this->reflectEntity->getAttributes(Url::class, \ReflectionAttribute::IS_INSTANCEOF);

        if(!is_array($attributes) OR count($attributes)<1){
            throw new MissingParameter('A Url must be set.');
        }

        $url = $attributes[0]->newInstance()->getUrl();

        $violations = $this->validator->validate($url,new ConstraintsUrl([
            'protocols' => ['http', 'https'],
        ]));

        if(count($violations)>0){
            throw new ConstraintViolation(sprintf('Problems with the Url %s',$url),$violations);
        }
        #$this->validateUrl($url);

        return $url;
    }

    /**
     * @throws OverrideExistingParameter
     * @throws WrongParameter
     * @throws ConstraintViolation
     * @throws MissingParameter
     */
    private function hydrateWithEntity():void
    {
        foreach ($this->getValuesFromEntity() as $type => $items){
            foreach ($items as $key => $item){
                if($type == Type::HEADER){
                    $this->addHeader($key,$item);
                }
                if($type == Type::JSON){
                    $this->addJson($key,$item);
                }
                if($type == Type::QUERY){
                    $this->addQuery($key,$item);
                }
            }
        }
    }
    /**
     * @throws MissingParameter
     * @throws WrongParameter
     * @throws OverrideExistingParameter
     * @throws ConstraintViolation
     */
    public function getRequest(): Request
    {
        $request = (new Request())
            ->setHttpMethod($this->getHttpMethod())
            ->setUrl($this->getUrl())
            ;

        if($this->getAuthentication()->getAuthenticationMethod() === "http-basic"){
            $request->setAuthBasic($this->getAuthentication()->getCredentials());
        }
        if($this->getAuthentication()->getAuthenticationMethod() === "token") {
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

        return $request;
    }

    /**
     * @throws WrongParameter
     */
    private function validateUrl($url): void
    {
        if (!(preg_match('/^(http|https):\/\/.*\.([a-z]{2,3})(\/[a-z0-9]+|\/?|)\/?$/mi', $url))) {
            throw new WrongParameter(sprintf('The Url %s is not valid', $url));
        }
    }

}