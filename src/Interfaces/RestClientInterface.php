<?php

namespace RestClient\Interfaces;

use RestClient\Dto\Request;
use RestClient\Helper\CacheHelper;
use RestClient\Helper\LoggerHelper;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface RestClientInterface
{

    public function __construct(HttpClientInterface $httpClient, SerializerInterface $serializer, ?LoggerHelper $loggerHelper, ?CacheHelper $cacheHelper);

    public function addRequest(Request $request);

    public function sendRequests(): self;

    public function iterateErrors(): array;

    public function hasErrors(): bool;

    public function iterateResults(): array;
}