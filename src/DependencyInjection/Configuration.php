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
     *     bus: 'listener_locator'
     *     queue: 'memory_unique'
     *     locator: 'named_event'
     *     name_resolver: 'event_class'
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        return (new TreeBuilder())
            ->root('gpslab_domain_event')
                ->children()
                    ->scalarNode('bus')
                        ->cannotBeEmpty()
                        ->defaultValue('listener_locator')
                    ->end()
                    ->scalarNode('queue')
                        ->cannotBeEmpty()
                        ->defaultValue('memory_unique')
                    ->end()
                    ->scalarNode('locator')
                        ->cannotBeEmpty()
                        ->defaultValue('named_event')
                    ->end()
                    ->scalarNode('name_resolver')
                        ->cannotBeEmpty()
                        ->defaultValue('event_class')
                    ->end()
                ->end()
            ->end()
        ;
    }
}
