<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $tree_builder = $this->createTreeBuilder('gpslab_domain_event');
        $root = $this->getRootNode($tree_builder, 'gpslab_domain_event');

        $bus = $root->children()->scalarNode('bus');
        $bus->cannotBeEmpty()->defaultValue('listener_located');

        $queue = $root->children()->scalarNode('queue');
        $queue->cannotBeEmpty()->defaultValue('pull_memory');

        $locator = $root->children()->scalarNode('locator');
        $locator->cannotBeEmpty()->defaultValue('symfony');

        $publish_on_flush = $root->children()->booleanNode('publish_on_flush');
        $publish_on_flush->defaultValue(false);

        return $tree_builder;
    }

    /**
     * @param string $name
     *
     * @return TreeBuilder
     */
    private function createTreeBuilder($name)
    {
        // Symfony 4.2 +
        if (method_exists(TreeBuilder::class, '__construct')) {
            return new TreeBuilder($name);
        }

        // Symfony 4.1 and below
        return new TreeBuilder();
    }

    /**
     * @param TreeBuilder $tree_builder
     * @param string      $name
     *
     * @return ArrayNodeDefinition
     */
    private function getRootNode(TreeBuilder $tree_builder, $name)
    {
        if (method_exists($tree_builder, 'getRootNode')) {
            // Symfony 4.2 +
            $root = $tree_builder->getRootNode();
        } else {
            // Symfony 4.1 and below
            $root = $tree_builder->root($name);
        }

        // @codeCoverageIgnoreStart
        if (!($root instanceof ArrayNodeDefinition)) { // should be always false
            throw new \RuntimeException(sprintf('The root node should be instance of %s, got %s instead.', ArrayNodeDefinition::class, get_class($root)));
        }
        // @codeCoverageIgnoreEnd

        return $root;
    }
}
