<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Request;
use RestClient\Interfaces\HandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class InformationalHandler implements HandlerInterface
{

    public function __construct(protected Request $request, protected ResponseInterface $response, protected ?SerializerInterface $serializer = null)
    {
    }

    public function getResult()
    {
        return $this->response->toArray();
    }
}