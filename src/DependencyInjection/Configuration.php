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
            ->scalarNode('namespacePräfix')->defaultValue('RestClient')->end()
            ->arrayNode('logging')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('logging')->defaultValue(true)->end()
            ->scalarNode('loglevel')->defaultValue('info')->end()
            ->end()->end()
            ->arrayNode('cache')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode("expiresAfter")->defaultValue(0)->end()
            ->floatNode("beta")->defaultValue(1.0)->info("higher values mean earlier recompute. 0 to disable early recompute. INF to force an immediate recompute")->end()
            ->end()->end()
            ->arrayNode('connections')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('baseurl')
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
