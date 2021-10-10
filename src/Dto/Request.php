<?php

declare(strict_types=1);

namespace RestClient\Dto;

class Request
{
    private string $httpMethod;
    private string $url;

    private ?array $auth_basic = null;
    private ?array $headers = null;
    private ?array $query = null;
    private ?array $json = null;

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

    public function addHeaders(string $fieldName, string $fieldValue): self
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

    public function addQuery(string $fieldName, string $fieldValue): self
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

    public function addJson(string $fieldName, string $fieldValue): self
    {
        if (!\is_array($this->json)) {
            $this->json = [];
        }
        $this->json = array_merge($this->json, [$fieldName => $fieldValue]);

        return $this;
    }
}
