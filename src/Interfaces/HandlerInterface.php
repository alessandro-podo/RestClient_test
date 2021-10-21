<?php

declare(strict_types=1);

namespace RestClient\Interfaces;

use RestClient\Dto\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class HandlerInterface
{
    public function __construct(protected Request $request, protected ResponseInterface $response, protected ?SerializerInterface $serializer = null)
    {
    }

    abstract public function getResult(): RestClientResponseInterface;

    /**
     * @throws TransportExceptionInterface
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }
}
