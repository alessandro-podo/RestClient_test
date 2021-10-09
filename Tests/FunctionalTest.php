<?php

namespace RestClientTests;

use PHPUnit\Framework\TestCase;
use RestClient\RequestBuilder;
use RestClient\RestClientBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new KnpULoremIpsumTestingKernel("test", true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $ipsum = $container->get('request.builder');
        $this->assertInstanceOf(RequestBuilder::class, $ipsum);
    }
}

class KnpULoremIpsumTestingKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new RestClientBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
    }
}