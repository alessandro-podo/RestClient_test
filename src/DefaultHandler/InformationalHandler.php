<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\Success;
use RestClient\Interfaces\HandlerInterface;
use RestClient\Interfaces\RestClientResponseInterface;

class InformationalHandler extends HandlerInterface
{


    public function getResult(): RestClientResponseInterface
    {
        return (new Success())->setBody($this->response->toArray())->setStatusCode($this->response->getStatusCode());
    }
}