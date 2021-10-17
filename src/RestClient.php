<?php

declare(strict_types=1);

namespace RestClient;

use Psr\Cache\CacheItemInterface;
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

    private bool $errors = false;

    public function __construct(
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
        private LoggerHelper        $loggerHelper,
        private CacheHelper         $cacheHelper
    )
    {
        //TODO: Cachen
        //TODO: Recursive helper, sollte jedoch im endzustand im Handler gemacht werden(RecursiveSuccessHandler als Abstract?)
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
     */
    public function sendRequests(): self
    {
        if (count($this->requests) === 0) {
            throw new MissingParameter('There is no Request added');
        }
        foreach ($this->requests as $request) {
            $this->receiveResponses($request);
        }

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
     */
    private function receiveResponses(Request $request): void
    {
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

    private function handleResponses(): void
    {
        foreach ($this->responses as $id => $response) {
            $handler = null;

            if (str_starts_with((string)$response->getStatusCode(), '1')) {
                $handler = $this->requests[$id]->getInformationalHandler();
                $handler = new $handler($this->requests[$id], $response);
            } elseif (str_starts_with((string)$response->getStatusCode(), '2')) {
                if ($this->requests[$id]->getCacheBeta() !== null and $this->requests[$id]->getCacheExpiresAfter() !== null) {
                    $handler = $this->cacheHelper->get($id, function (CacheItemInterface $item) use ($id, $response) {
                        $item->expiresAfter($this->requests[$id]->getCacheExpiresAfter());

                        $handler = $this->requests[$id]->getSuccessHandler();
                        return new $handler($this->requests[$id], $response, $this->serializer);
                    }, $this->requests[$id]->getCacheBeta());
                } else {
                    $handler = $this->requests[$id]->getSuccessHandler();
                    $handler = new $handler($this->requests[$id], $response, $this->serializer);
                }
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
                $this->handledResponsesSuccess[$id] = $handler->getResult();
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
