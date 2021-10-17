<?php

namespace RestClient\Helper;

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
}