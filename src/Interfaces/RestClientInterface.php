<?php

namespace RestClient\Interfaces;

use Psr\Log\LoggerInterface;
use RestClient\Dto\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface RestClientInterface
{

    public function __construct(HttpClientInterface $httpClient, SerializerInterface $serializer, ?LoggerInterface $logger);

    public function addRequest(Request $request);

    public function sendRequests();

    public function iterateErrors();

    public function hasErrors(): bool;

    public function iterateResults();
    #public function setLogger(LoggerInterface $logger);
}