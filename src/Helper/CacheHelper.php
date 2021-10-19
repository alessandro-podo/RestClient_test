<?php

namespace RestClient\Helper;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class CacheHelper
{
    public function __construct(
        private CacheInterface        $cache,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $id, \Closure $param, ?float $getCacheBeta): mixed
    {
        return $this->cache->get($id, $param, $getCacheBeta);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getFromCache(string $id): mixed
    {
        if (!$this->isInCache($id)) {
            return null;
        }
        return $this->get($id, function () {
            return null;
        }, null);
    }

    public function isInCache(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }
}