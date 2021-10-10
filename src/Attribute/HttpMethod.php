<?php

declare(strict_types=1);

namespace RestClient\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class HttpMethod
{
    const GET = 'GET';
    const POST = 'POST';
    const DELETE = 'DELETE';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    public string $method;

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
