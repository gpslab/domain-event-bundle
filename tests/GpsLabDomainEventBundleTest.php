<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\EventListenerPass;
use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use GpsLab\Bundle\DomainEvent\GpsLabDomainEventBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GpsLabDomainEventBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GpsLabDomainEventBundle
     */
    private $bundle;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->bundle = new GpsLabDomainEventBundle();
        $this->container = new ContainerBuilder();
    }

    public function testCorrectBundle()
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testBuild()
    {
        $this->bundle->build($this->container);

        $has_event_listener_pass = false;
        foreach ($this->container->getCompiler()->getPassConfig()->getBeforeOptimizationPasses() as $pass) {
            $has_event_listener_pass = $pass instanceof EventListenerPass ?: $has_event_listener_pass;
        }
        $this->assertTrue($has_event_listener_pass);
    }

    public function testContainerExtension()
    {
        $this->assertInstanceOf(GpsLabDomainEventExtension::class, $this->bundle->getContainerExtension());
    }
}
