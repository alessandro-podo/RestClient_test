<?php

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\ClientError;
use RestClient\Dto\Http\Error;
use RestClient\Interfaces\HandlerInterface;
use RuntimeException;

class ClientHandler extends HandlerInterface
{
    /**
     * @throws RuntimeException
     */
    public function getResult(): Error
    {
        try {
            $this->response->toArray();
        } catch (\Throwable $throwable) {
            return new ClientError($throwable->getMessage(), $throwable->getCode(), $this->request, $throwable);
        }

        throw new RuntimeException('There should be an Error');
    }
}