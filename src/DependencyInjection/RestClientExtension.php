<?php

declare(strict_types=1);

namespace RestClient\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class RestClientExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter('rest_client.'.$key, $value);
        }

        if(isset($config["logger"])){
            $container->getDefinition('helper.logger')->setArgument('$logger',new Reference($config["logger"],ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE));
        }
        if(isset($config["cacher"])){
            $container->getDefinition('helper.cache')->setArgument('$cache',new Reference($config["cacher"],ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE));
        }
    }

    public function getAlias()
    {
        return 'rest_client';
    }
}
