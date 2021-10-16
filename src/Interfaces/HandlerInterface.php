<?php

namespace RestClient\Interfaces;


use RestClient\Dto\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface HandlerInterface
{
    public function __construct(Request $request, ResponseInterface $response, ?SerializerInterface $serializer = null);

    public function getResult();
}