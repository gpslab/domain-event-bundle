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
     *     bus: 'listener_located'
     *     queue: 'pull_memory'
     *     locator: 'symfony'
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
                        ->defaultValue('listener_located')
                    ->end()
                    ->scalarNode('queue')
                        ->cannotBeEmpty()
                        ->defaultValue('pull_memory')
                    ->end()
                    ->scalarNode('locator')
                        ->cannotBeEmpty()
                        ->defaultValue('symfony')
                    ->end()
                ->end()
            ->end()
        ;
    }
}
