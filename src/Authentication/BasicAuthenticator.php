<?php

declare(strict_types=1);

namespace RestClient\Authentication;

#[\Attribute(\Attribute::TARGET_CLASS)]
class BasicAuthenticator implements \RestClient\Interfaces\Authenticator
{
    private array $credentials;

    public function __construct(string $username, string $password)
    {
        $this->credentials[0] = $username;
        $this->credentials[1] = $password;
    }

    public function getAuthenticationMethod(): string
    {
        return 'http-basic';
    }

    public function setUsername($username): self
    {
        $this->credentials[0] = $username;

        return $this;
    }

    public function setPassword($password): self
    {
        $this->credentials[1] = $password;

        return $this;
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }
}
