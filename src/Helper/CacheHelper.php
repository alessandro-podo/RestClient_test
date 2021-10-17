<?php

namespace RestClient\Helper;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CacheHelper
{
    public function __construct(
        private CacheInterface        $cache,
        private ParameterBagInterface $parameterBag
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(int|string $id, \Closure $param, ?float $getCacheBeta): mixed
    {
        return $this->cache->get($id, $param, $getCacheBeta);
    }
}