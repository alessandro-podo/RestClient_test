<?php

declare(strict_types=1);

namespace RestClient;

use RestClient\Interfaces\RequestBuilderInterface;
use RestClient\Interfaces\RestClientInterface;

class ShortRestClient
{
    public function __construct(private RestClientInterface $restClient, private RequestBuilderInterface $requestBuilder)
    {
    }

    public function quickSend(object $entity): object
    {
        $request = $this->requestBuilder->setEntity($entity)->getRequest();
        $response = $this->restClient->addRequest($request)->sendRequests();

        if ($response->hasErrors()) {
            return current($response->iterateErrors());
        }

        return current($response->iterateResults());
    }
}
