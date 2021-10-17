<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\RestClientResponse;
use RestClient\Interfaces\HandlerInterface;
use RestClient\Interfaces\RestClientResponseInterface;

class InformationalHandler extends HandlerInterface
{


    public function getResult(): RestClientResponseInterface
    {
        return (new RestClientResponse())->setBody($this->response->toArray())->setStatusCode($this->response->getStatusCode());
    }
}