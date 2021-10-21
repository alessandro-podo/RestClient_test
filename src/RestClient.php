<?php

declare(strict_types=1);

namespace RestClient;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use RestClient\Dto\Http\Error;
use RestClient\Dto\Request;
use RestClient\Exceptions\MissingParameter;
use RestClient\Helper\CacheHelper;
use RestClient\Helper\LoggerHelper;
use RestClient\Interfaces\HandlerInterface;
use RestClient\Interfaces\RestClientInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RestClient implements RestClientInterface
{
    /** @var Request[] */
    private array $requests = [];
    /** @var ResponseInterface[] */
    private array $responses = [];
    private array $handledResponsesSuccess = [];
    private array $handledResponsesErrors = [];
    private array $handledCachedResponsesSuccess = [];

    private bool $errors = false;

    public function __construct(
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
        private LoggerHelper        $loggerHelper,
        private CacheHelper         $cacheHelper
    )
    {
    }

    /*
     * Request with the same Id will be overwritten
     */
    public function addRequest(Request $request): self
    {
        $this->requests[$request->getId()] = $request;
        return $this;
    }

    /**
     * @throws MissingParameter
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function sendRequests(): self
    {
        if (count($this->requests) === 0) {
            throw new MissingParameter('There is no Request added');
        }
        foreach ($this->requests as $request) {
            if ($request->isRefreshCache()) {
                $this->cacheHelper->delete($request->getId());
            }
            $this->receiveResponses($request);
        }

        $this->handleCachedResponses();
        $this->handleResponses();
        return $this;
    }

    public function iterateErrors(): array
    {
        return $this->handledResponsesErrors;
    }

    public function hasErrors(): bool
    {
        return $this->errors;
    }

    public function iterateResults(): array
    {
        return $this->handledResponsesSuccess;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    private function receiveResponses(Request $request): void
    {
        //if Request in Cache, id dem resonsesCached Stack hinzufügen
        if ($this->cacheHelper->isInCache($request->getId())) {
            $this->handledCachedResponsesSuccess[$request->getId()] = $this->cacheHelper->getFromCache($request->getId());
            return;
        }
        $options = [];
        if ($request->getJson() !== null) {
            $options = array_merge($options, $request->getJson());
        }
        if ($request->getQuery() !== null) {
            $options = array_merge($options, $request->getQuery());
        }
        if ($request->getAuthBasic() !== null) {
            $options = array_merge($options, $request->getAuthBasic());
        }
        if ($request->getHeaders() !== null) {
            $options = array_merge($options, $request->getHeaders());
        }

        $this->responses[$request->getId()] = $this->httpClient->request($request->getHttpMethod(), $request->getUrl(), $options);
    }

    private function handleCachedResponses(): void
    {
        //handledResponsesSuccess dem Stack hinzufügen
        //TODO ist es ein Problem, dass die Request nicht in der gleichen Reihenfolge sind?
        foreach ($this->handledCachedResponsesSuccess as $id => $cachedResponsesSuccess) {
            $this->handledResponsesSuccess[$id] = $cachedResponsesSuccess;
            $url = "From Cache: " . $this->requests[$id]->getUrl();
            $this->loggerHelper->log($this->requests[$id]->setUrl($url), $cachedResponsesSuccess);
        }
    }

    private function handleResponses(): void
    {
        foreach ($this->responses as $id => $response) {
            $handler = null;

            if (str_starts_with((string)$response->getStatusCode(), '1')) {
                $handler = $this->requests[$id]->getInformationalHandler();
                $handler = new $handler($this->requests[$id], $response);
            } elseif (str_starts_with((string)$response->getStatusCode(), '2')) {
                $handler = $this->requests[$id]->getSuccessHandler();
                $handler = new $handler($this->requests[$id], $response, $this->serializer);
            } elseif (str_starts_with((string)$response->getStatusCode(), '3')) {
                $handler = $this->requests[$id]->getRedirectionHandler();
                $handler = new $handler($this->requests[$id], $response);
            } elseif (str_starts_with((string)$response->getStatusCode(), '4')) {
                $handler = $this->requests[$id]->getClientHandler();
                $handler = new $handler($this->requests[$id], $response);
            } elseif (str_starts_with((string)$response->getStatusCode(), '5')) {
                $handler = $this->requests[$id]->getServerHandler();
                $handler = new $handler($this->requests[$id], $response);
            }
            if ($handler === null) {
                throw new \RuntimeException('No Handler');
            }

            if (!$handler instanceof HandlerInterface) {
                throw new \RuntimeException(sprintf('Handler ( %s ) must implement %s', get_class($handler), HandlerInterface::class));
            }

            if ($handler->getResult() instanceof Error) {
                $this->handledResponsesErrors[$id] = $handler->getResult();
                $this->errors = true;
            } else {
                //Result in/aus dem Cache
                #$this->handledResponsesSuccess[$id] = $handler->getResult();
                dump($id);
                $this->handledResponsesSuccess[$id] = $this->cacheHelper->get($id, function (CacheItemInterface $item) use ($id, $handler) {
                    $item->expiresAfter($this->requests[$id]->getCacheExpiresAfter());
                    return $handler->getResult();
                }, $this->requests[$id]->getCacheBeta());
            }

            $this->loggerHelper->log($this->requests[$id], $handler->getResult());

        }
    }

    public function setLoggen(bool $loggen): self
    {
        $this->loggerHelper->setLoggen($loggen);

        return $this;
    }

    public function reset(): self
    {
        $this->loggerHelper->reset();

        return $this;
    }
}
