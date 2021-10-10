<?php

declare(strict_types=1);

namespace RestClient\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class ApiEndpoint
{
    public string $apiEndpoint;

    public function __construct(string $apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }
}
