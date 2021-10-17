<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\Error;
use RestClient\Dto\Http\ServerError;
use RestClient\Interfaces\HandlerInterface;

class ServerHandler extends HandlerInterface
{
    public function getResult(): Error
    {
        try {
            $this->response->toArray();
        } catch (\Throwable $throwable) {
            return (new ServerError($throwable->getMessage(), $throwable->getCode(), $this->request, $throwable))->setStatusCode($this->response->getStatusCode());
        }

        throw new \RuntimeException('There should be an Error');
    }
}