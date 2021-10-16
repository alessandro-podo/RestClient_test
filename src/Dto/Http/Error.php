<?php

namespace RestClient\Dto\Http;

use RestClient\Dto\Request;
use Throwable;

class Error
{

    public function __construct(private $message, private $code, private Request $request, private Throwable $previous)
    {
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCode()
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