<?php

namespace RestClient\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Url
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }


}