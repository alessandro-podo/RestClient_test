<?php

declare(strict_types=1);

namespace RestClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder('rest_client');
        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('connections')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('url')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('username')->end()
            ->scalarNode('password')->end()
            ->scalarNode('keyField')->end()
            ->scalarNode('keyValue')->end()
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
