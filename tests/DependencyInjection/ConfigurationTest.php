<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\DependencyInjection;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ScalarNode;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    protected function setUp()
    {
        $this->configuration = new Configuration();
    }

    public function testConfigTree()
    {
        $tree_builder = $this->configuration->getConfigTreeBuilder();

        $this->assertInstanceOf(TreeBuilder::class, $tree_builder);

        /* @var $tree ArrayNode */
        $tree = $tree_builder->buildTree();

        $this->assertInstanceOf(ArrayNode::class, $tree);
        $this->assertEquals('gpslab_domain_event', $tree->getName());

        /* @var $children ScalarNode[] */
        $children = $tree->getChildren();

        $this->assertInternalType('array', $children);
        $this->assertEquals(['bus', 'queue', 'locator'], array_keys($children));

        $this->assertInstanceOf(ScalarNode::class, $children['bus']);
        $this->assertEquals('listener_located', $children['bus']->getDefaultValue());
        $this->assertFalse($children['bus']->isRequired());

        $this->assertInstanceOf(ScalarNode::class, $children['queue']);
        $this->assertEquals('pull_memory', $children['queue']->getDefaultValue());
        $this->assertFalse($children['queue']->isRequired());

        $this->assertInstanceOf(ScalarNode::class, $children['locator']);
        $this->assertEquals('symfony', $children['locator']->getDefaultValue());
        $this->assertFalse($children['locator']->isRequired());
    }
}
