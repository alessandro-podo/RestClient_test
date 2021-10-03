<?php

namespace RestClient\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class HttpMethod
{
    public string $method;

    const GET='GET';
    const POST='POST';
    const DELETE='DELETE';
    const PUT='PUT';
    const PATCH='PATCH';

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }


}