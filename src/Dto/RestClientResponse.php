<?php

namespace RestClient\Dto;

use RestClient\Interfaces\RestClientResponseInterface;

class RestClientResponse extends RestClientResponseInterface
{

    private ?array $body;

    public function getBody(): ?array
    {
        return $this->body;
    }

    public function setBody(?array $body): RestClientResponse
    {
        $this->body = $body;
        return $this;
    }

}