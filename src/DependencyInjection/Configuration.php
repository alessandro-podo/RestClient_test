<?php

namespace RestClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {

        /*security_headers:
          frames: sameorigin
          sniff_mimes: false
          https:
            required: true
            subdomains: true
            preload: true
          content:
            default: self
            scripts:
              - self
              - https://www.googletagmanager.com
              - https://kit.fontawesome.com
            styles:
              - self
              - https://fonts.googleapis.com
            styles_inline: true
            upgrade_insecure: true

         $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('frames')->end()
            ->scalarNode('sniff_mimes')->end()
            ->arrayNode('https')->children()
                ->booleanNode('required')->end()
                ->booleanNode('subdomains')->end()
                ->booleanNode('preload')->end()
            ->end()
            ->end()
            ->arrayNode('content')->children()
                ->scalarNode('default')->end()
                ->scalarNode('upgrade_insecure')->end()
                ->scalarNode('styles_inline')->end()
                ->arrayNode('scripts')->scalarPrototype()->end()->end()
                ->arrayNode('styles')->scalarPrototype()->end()->end()
            ->end()
            ->end();

        */

        $treeBuilder = new TreeBuilder('rest_client');
        $treeBuilder->getRootNode()
            ->children()
            ->integerNode("test")->defaultValue(4)->end()
            ->arrayNode('configs')->children()
            ->scalarNode('default')->end()
            ->arrayNode('scripts')->scalarPrototype()->end()->end()
            ->arrayNode('config')->children()
            ->scalarNode('default')->isRequired()->end()
            ->arrayNode('scripts')->scalarPrototype()->end()->end()/*->arrayNode('connections')
                #->useAttributeAsKey('name')
                #->arrayPrototype()
                    ->children()
                        ->scalarNode('table')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                #->end()
                ->end()
            ->end()*/
        ;


        return $treeBuilder;
    }
}