<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\Error;
use RestClient\Dto\Http\RedirectionError;
use RestClient\Dto\Request;
use RestClient\Interfaces\HandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RedirectionHandler implements HandlerInterface
{

    public function __construct(protected Request $request, protected ResponseInterface $response, protected ?SerializerInterface $serializer = null)
    {
    }

    public function getResult(): Error
    {
        try {
            $this->response->toArray();
        } catch (\Throwable $throwable) {
            return new RedirectionError($throwable->getMessage(), $throwable->getCode(), $this->request, $throwable);
        }

        throw new \RuntimeException('There should be an Error');
    }
}