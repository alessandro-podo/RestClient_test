<?php

namespace RestClient\Attribute;

use Attribute;

#[Attribute(\Attribute::TARGET_CLASS)]
class Handler
{
    public function __construct(
        private ?string $informationalHandler = null,
        private ?string $successHandler = null,
        private ?string $redirectionHandler = null,
        private ?string $clientHandler = null,
        private ?string $serverHandler = null
    )
    {
    }

    public function getInformationalHandler(): ?string
    {
        return $this->informationalHandler;
    }

    public function getSuccessHandler(): ?string
    {
        return $this->successHandler;
    }

    public function getRedirectionHandler(): ?string
    {
        return $this->redirectionHandler;
    }

    public function getClientHandler(): ?string
    {
        return $this->clientHandler;
    }

    public function getServerHandler(): ?string
    {
        return $this->serverHandler;
    }

}