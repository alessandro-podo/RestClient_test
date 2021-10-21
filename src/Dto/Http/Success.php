<?php

declare(strict_types=1);

namespace RestClient\Dto\Http;

use RestClient\Interfaces\RestClientResponseInterface;

class Success extends RestClientResponseInterface
{
    private ?array $body;

    public function getBody(): ?array
    {
        return $this->body;
    }

    public function setBody(?array $body): self
    {
        $this->body = $body;

        return $this;
    }
}
