<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\Success;
use RestClient\Interfaces\HandlerInterface;
use RestClient\Interfaces\RestClientResponseInterface;

class SuccessHandler extends HandlerInterface
{
    public function getResult(): RestClientResponseInterface
    {
        try {
            return (new Success())->setBody($this->response->toArray())->setStatusCode($this->response->getStatusCode());
        } catch (\Throwable $throwable) {
            return (new Success())->setBody(null)->setStatusCode($this->response->getStatusCode());
        }
    }

}