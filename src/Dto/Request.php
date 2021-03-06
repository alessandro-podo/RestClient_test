<?php

declare(strict_types=1);

namespace RestClient\Dto;

use RestClient\DefaultHandler\ClientHandler;
use RestClient\DefaultHandler\InformationalHandler;
use RestClient\DefaultHandler\RedirectionHandler;
use RestClient\DefaultHandler\ServerHandler;
use RestClient\DefaultHandler\SuccessHandler;

class Request
{
    private string $httpMethod;
    private string $url;
    private string $id;

    private ?int $cacheExpiresAfter = null;
    private ?float $cacheBeta = null;
    private bool $refreshCache = false;

    private ?array $auth_basic = null;
    private ?array $headers = null;
    private ?array $query = null;
    private ?array $json = null;

    private string $informationalHandler = InformationalHandler::class;
    private string $successHandler = SuccessHandler::class;
    private string $redirectionHandler = RedirectionHandler::class;
    private string $clientHandler = ClientHandler::class;
    private string $serverHandler = ServerHandler::class;

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function setHttpMethod(string $httpMethod): self
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCacheExpiresAfter(): ?int
    {
        return $this->cacheExpiresAfter;
    }

    public function setCacheExpiresAfter(?int $cacheExpiresAfter): self
    {
        $this->cacheExpiresAfter = $cacheExpiresAfter;

        return $this;
    }

    public function getCacheBeta(): ?float
    {
        return $this->cacheBeta;
    }

    public function setCacheBeta(?float $cacheBeta): self
    {
        $this->cacheBeta = $cacheBeta;

        return $this;
    }

    public function isRefreshCache(): bool
    {
        return $this->refreshCache;
    }

    public function setRefreshCache(bool $refreshCache): self
    {
        $this->refreshCache = $refreshCache;

        return $this;
    }

    public function getAuthBasic(): ?array
    {
        return isset($this->auth_basic) ? ['auth_basic' => $this->auth_basic] : null;
    }

    public function setAuthBasic(?array $auth_basic): self
    {
        $this->auth_basic = $auth_basic;

        return $this;
    }

    public function getHeaders(): ?array
    {
        return isset($this->headers) ? ['headers' => $this->headers] : null;
    }

    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function addHeaders(string $fieldName, mixed $fieldValue): self
    {
        if (!\is_array($this->headers)) {
            $this->headers = [];
        }
        $this->headers = array_merge($this->headers, [$fieldName => $fieldValue]);

        return $this;
    }

    public function getQuery(): ?array
    {
        return isset($this->query) ? ['query' => $this->query] : null;
    }

    public function setQuery(?array $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function addQuery(string $fieldName, mixed $fieldValue): self
    {
        if (!\is_array($this->query)) {
            $this->query = [];
        }
        $this->query = array_merge($this->query, [$fieldName => $fieldValue]);

        return $this;
    }

    public function getJson(): ?array
    {
        return isset($this->json) ? ['json' => $this->json] : null;
    }

    public function setJson(?array $json): self
    {
        $this->json = $json;

        return $this;
    }

    public function addJson(string $fieldName, mixed $fieldValue): self
    {
        if (!\is_array($this->json)) {
            $this->json = [];
        }
        $this->json = array_merge($this->json, [$fieldName => $fieldValue]);

        return $this;
    }

    public function getInformationalHandler(): string
    {
        return $this->informationalHandler;
    }

    public function setInformationalHandler(string $informationalHandler): self
    {
        $this->informationalHandler = $informationalHandler;

        return $this;
    }

    public function getSuccessHandler(): string
    {
        return $this->successHandler;
    }

    public function setSuccessHandler(string $successHandler): self
    {
        $this->successHandler = $successHandler;

        return $this;
    }

    public function getRedirectionHandler(): string
    {
        return $this->redirectionHandler;
    }

    public function setRedirectionHandler(string $redirectionHandler): self
    {
        $this->redirectionHandler = $redirectionHandler;

        return $this;
    }

    public function getClientHandler(): string
    {
        return $this->clientHandler;
    }

    public function setClientHandler(string $clientHandler): self
    {
        $this->clientHandler = $clientHandler;

        return $this;
    }

    public function getServerHandler(): string
    {
        return $this->serverHandler;
    }

    public function setServerHandler(string $serverHandler): self
    {
        $this->serverHandler = $serverHandler;

        return $this;
    }
}
