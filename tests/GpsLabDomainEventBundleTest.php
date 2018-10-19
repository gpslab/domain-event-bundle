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

    protected function setUp()
    {
        $this->bundle = new GpsLabDomainEventBundle();
    }

    public function testCorrectBundle()
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testBuild()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped(sprintf('Impossible to mock "%s" on PHP 7', ContainerBuilder::class));
        }

        /* @var $container \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder */
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(EventListenerPass::class));
        $this->bundle->build($container);
    }

    public function testContainerExtension()
    {
        $this->assertInstanceOf(GpsLabDomainEventExtension::class, $this->bundle->getContainerExtension());
    }
}
