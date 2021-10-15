<?php

namespace RestClient\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Cache
{

    public function __construct(private ?int $cacheExpiresAfter = null, private ?float $cacheBeta = null)
    {
    }

    public function getCacheExpiresAfter(): ?int
    {
        return $this->cacheExpiresAfter;
    }

    public function getCacheBeta(): ?float
    {
        return $this->cacheBeta;
    }


}