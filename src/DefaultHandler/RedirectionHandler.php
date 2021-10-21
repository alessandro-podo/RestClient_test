<?php

declare(strict_types=1);

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\Error;
use RestClient\Dto\Http\RedirectionError;
use RestClient\Interfaces\HandlerInterface;

class RedirectionHandler extends HandlerInterface
{
    public function getResult(): Error
    {
        try {
            $this->response->toArray();
        } catch (\Throwable $throwable) {
            return (new RedirectionError($throwable->getMessage(), $throwable->getCode(), $this->request, $throwable))->setStatusCode($this->response->getStatusCode());
        }

        throw new \RuntimeException('There should be an Error');
    }
}
