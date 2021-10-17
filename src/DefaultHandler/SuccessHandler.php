<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\RestClientResponse;
use RestClient\Interfaces\HandlerInterface;
use RestClient\Interfaces\RestClientResponseInterface;

class SuccessHandler extends HandlerInterface
{
    public function getResult(): RestClientResponseInterface
    {
        try {
            return (new RestClientResponse())->setBody($this->response->toArray())->setStatusCode($this->response->getStatusCode());
        } catch (\Throwable $throwable) {
            return (new RestClientResponse())->setBody(null)->setStatusCode($this->response->getStatusCode());
        }
    }

}