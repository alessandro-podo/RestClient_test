<?php

declare(strict_types=1);

namespace RestClient\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Type
{
    const HEADER = 'HEADER';
    const JSON = 'JSON';
    const QUERY = 'QUERY';
    const URLREPLACE = 'URLREPLACE';

    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
