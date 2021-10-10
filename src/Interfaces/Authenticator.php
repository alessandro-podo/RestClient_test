<?php

declare(strict_types=1);

namespace RestClient\Interfaces;

#[\Attribute(\Attribute::TARGET_CLASS)]
interface Authenticator
{
    public function __construct(string $username, string $password);

    public function getAuthenticationMethod(): string;

    public function getCredentials(): array;
}
