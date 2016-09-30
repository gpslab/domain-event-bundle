<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Bundle\DomainEvent\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Config tree builder.
     *
     * Example config:
     *
     * gpslab_domain_event:
     *     locator: 'named_event'
     *     name_resolver: 'event_class'
     *     doctrine:
     *         handle_events:
     *             - 'prePersist'
     *             - 'preUpdate'
     *             - 'preRemove'
     *             - 'preFlush'
     *         connections:
     *             - 'default'
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        return (new TreeBuilder())
            ->root('gpslab_domain_event')
                ->children()
                    ->scalarNode('locator')
                        ->cannotBeEmpty()
                        ->defaultValue('named_event')
                    ->end()
                    ->scalarNode('name_resolver')
                        ->cannotBeEmpty()
                        ->defaultValue('event_class')
                    ->end()
                    ->arrayNode('doctrine')
                        ->children()
                            ->arrayNode('handle_events')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('connections')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
