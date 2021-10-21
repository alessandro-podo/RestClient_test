<?php

declare(strict_types=1);

namespace RestClient\Dto\Http;

use RestClient\Dto\Request;
use RestClient\Interfaces\RestClientResponseInterface;
use Throwable;

class Error extends RestClientResponseInterface
{
    public function __construct(private string $message, private int $code, private Request $request, private Throwable $previous)
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getPrevious(): Throwable
    {
        return $this->previous;
    }
}
