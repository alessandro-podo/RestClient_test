<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Request;
use RestClient\Interfaces\HandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SuccessHandler implements HandlerInterface
{
    public function __construct(private Request $request, private ResponseInterface $response, ?SerializerInterface $serializer = null)
    {
    }

    public function getResult(): ?array
    {
        try {
            return $this->response->toArray();
        } catch (\Throwable $throwable) {
            return null;
        }
    }

}