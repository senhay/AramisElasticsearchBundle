<?php

namespace Aramis\Bundle\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aramis_elasticsearch', 'array');

        $rootNode
            ->children()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->end()
                        ->scalarNode('type')->end()
                        ->scalarNode('type_general')->end()
                        ->arrayNode('offer_index')
                            ->children()
                                ->scalarNode('fr')->end()
                                ->scalarNode('de')->end()
                                ->scalarNode('es')->end()
                            ->end()
                        ->end()
                        ->arrayNode('general_index')
                            ->children()
                                ->scalarNode('fr')->end()
                                ->scalarNode('de')->end()
                                ->scalarNode('es')->end()
                            ->end()
                        ->end()
                        ->arrayNode('rabbitmq_river')
                            ->children()
                                ->scalarNode('queue')->end()
                                ->scalarNode('exchange')->end()
                                ->scalarNode('routing_key')->end()
                                ->scalarNode('exchange_type')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('rabbitmq')
                    ->children()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('vhost')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
