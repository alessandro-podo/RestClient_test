<?php

declare(strict_types=1);

namespace RestClient\Interfaces;

abstract class RestClientResponseInterface
{
    private int $statusCode;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }
}
