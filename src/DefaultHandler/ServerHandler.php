<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\Error;
use RestClient\Dto\Http\ServerError;
use RestClient\Dto\Request;
use RestClient\Interfaces\HandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ServerHandler implements HandlerInterface
{

    public function __construct(private Request $request, private ResponseInterface $response, ?SerializerInterface $serializer = null)
    {
    }

    public function getResult(): Error
    {
        try {
            $this->response->toArray();
        } catch (\Throwable $throwable) {
            return new ServerError($throwable->getMessage(), $throwable->getCode(), $this->request, $throwable);
        }

        throw new \RuntimeException('There should be an Error');
    }
}