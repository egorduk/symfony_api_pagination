<?php

namespace Btc\PaginationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('btc_pagination');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('navigation')
                        ->defaultValue('BtcPaginationBundle:Pagination:navigation.html.twig')
                        ->end()
                        ->scalarNode('filter')
                        ->defaultValue('BtcPaginationBundle:Pagination:filter.html.twig')
                        ->end()
                        ->scalarNode('sorting')
                        ->defaultValue('BtcPaginationBundle:Pagination:sorting.html.twig')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
