<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\NamedEventListenerPass;
use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\VoterListenerPass;
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
        /* @var $container \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder */
        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(NamedEventListenerPass::class));
        $container
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(VoterListenerPass::class));

        $this->bundle->build($container);
    }
}
