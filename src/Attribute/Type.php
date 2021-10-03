<?php

namespace RestClient\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Type
{
    const HEADER = 'HEADER';
    const JSON = 'JSON';
    const QUERY = 'QUERY';

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