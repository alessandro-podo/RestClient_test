<?php

declare(strict_types=1);

namespace RestClient\DefaultHandler;

use RestClient\Dto\Http\ClientError;
use RestClient\Dto\Http\Error;
use RestClient\Interfaces\HandlerInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ClientHandler extends HandlerInterface
{
    /**
     * @throws RuntimeException
     * @throws TransportExceptionInterface
     */
    public function getResult(): Error
    {
        try {
            $this->response->toArray();
        } catch (\Throwable $throwable) {
            return (new ClientError($throwable->getMessage(), $throwable->getCode(), $this->request, $throwable))->setStatusCode($this->response->getStatusCode());
        }

        throw new RuntimeException('There should be an Error');
    }
}
