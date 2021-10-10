<?php

declare(strict_types=1);

namespace RestClient\Authentication;

#[\Attribute(\Attribute::TARGET_CLASS)]
class TokenAuthenticator implements \RestClient\Interfaces\Authenticator
{
    private array $credentials;

    public function __construct(string $headerFieldName, string $headerFieldValue)
    {
        $this->credentials[0] = $headerFieldName;
        $this->credentials[1] = $headerFieldValue;
    }

    public function getAuthenticationMethod(): string
    {
        return 'token';
    }

    public function setHeaderFieldName($fieldName): self
    {
        $this->credentials[0] = $fieldName;

        return $this;
    }

    public function setHeaderFieldValue($fieldValue): self
    {
        $this->credentials[1] = $fieldValue;

        return $this;
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }
}
