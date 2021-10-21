<?php

declare(strict_types=1);

namespace RestClient\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Cache
{
    /**
     * @param null|int   $cacheExpiresAfter in seconds
     * @param null|float $cacheBeta         By default the beta is 1.0 and higher values mean earlier recompute. Set it to 0 to disable early recompute and set it to INF to force an immediate recompute:
     */
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
