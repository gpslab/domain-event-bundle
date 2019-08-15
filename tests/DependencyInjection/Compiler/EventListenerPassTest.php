<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\DependencyInjection\Compiler;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\EventListenerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EventListenerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private $container;

    /**
     * @var EventListenerPass
     */
    private $pass;

    protected function setUp()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped(sprintf('Impossible to mock "%s" on PHP 7', ContainerBuilder::class));
        }

        $this->container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->pass = new EventListenerPass();
    }

    public function testProcessNoLocator()
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('domain_event.locator')
            ->will($this->returnValue(false))
        ;
        $this->container
            ->expects($this->never())
            ->method('findDefinition')
        ;
        $this->container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
        ;

        $this->pass->process($this->container);
    }

    public function testProcessCustomLocator()
    {
        // create fake definitions to distinguish them
        $symfony_locator = new Definition(null, ['symfony']);
        $container_locator = new Definition(null, ['container']);
        $current_locator = new Definition(null, ['custom']);

        $this->container
            ->expects($this->at(0))
            ->method('has')
            ->with('domain_event.locator')
            ->will($this->returnValue(true))
        ;
        $this->container
            ->expects($this->at(1))
            ->method('findDefinition')
            ->with('domain_event.locator')
            ->will($this->returnValue($current_locator))
        ;
        $this->container
            ->expects($this->at(2))
            ->method('findDefinition')
            ->with('domain_event.locator.symfony')
            ->will($this->returnValue($symfony_locator))
        ;
        $this->container
            ->expects($this->at(3))
            ->method('findDefinition')
            ->with('domain_event.locator.container')
            ->will($this->returnValue($container_locator))
        ;
        $this->container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
        ;

        $this->pass->process($this->container);
    }

    /**
     * @return array
     */
    public function locators()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped(sprintf('Impossible to mock "%s" on PHP 7', Definition::class));
        }

        $locator = $this->getMock(Definition::class);

        return [
            [
                $locator,
                new Definition(),
                $locator,
            ],
            [
                new Definition(),
                $locator,
                $locator,
            ],
        ];
    }

    /**
     * @dataProvider locators
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Definition $symfony_locator
     * @param \PHPUnit_Framework_MockObject_MockObject|Definition $container_locator
     * @param \PHPUnit_Framework_MockObject_MockObject|Definition $current_locator
     */
    public function testProcess(
        Definition $symfony_locator,
        Definition $container_locator,
        Definition $current_locator
    ) {
        $listeners = [
            'foo' => [
                ['event' => 'PurchaseOrderCompletedEvent', 'method' => 'onPurchaseOrderCompleted'],
                ['event' => 'PurchaseOrderCreated', 'method' => 'onPurchaseOrderCreated'],
            ],
            'bar' => [
                ['event' => 'PurchaseOrderCompletedEvent'],
            ],
            'baz' => [
                ['event' => 'PurchaseOrderCreated', 'method' => 'handle'],
            ],
        ];
        $subscribers = [
            'foo' => [],
            'bar' => [],
            'baz' => [],
        ];

        $locator_index = 0;
        $container_index = 0;

        $this->container
            ->expects($this->at($container_index++))
            ->method('has')
            ->with('domain_event.locator')
            ->will($this->returnValue(true))
        ;
        $this->container
            ->expects($this->at($container_index++))
            ->method('findDefinition')
            ->with('domain_event.locator')
            ->will($this->returnValue($current_locator))
        ;
        $this->container
            ->expects($this->at($container_index++))
            ->method('findDefinition')
            ->with('domain_event.locator.symfony')
            ->will($this->returnValue($symfony_locator))
        ;
        $this->container
            ->expects($this->at($container_index++))
            ->method('findDefinition')
            ->with('domain_event.locator.container')
            ->will($this->returnValue($container_locator))
        ;
        $this->container
            ->expects($this->at($container_index++))
            ->method('findTaggedServiceIds')
            ->with('domain_event.listener')
            ->will($this->returnValue($listeners))
        ;
        $this->container
            ->expects($this->at($container_index++))
            ->method('findTaggedServiceIds')
            ->with('domain_event.subscriber')
            ->will($this->returnValue($subscribers))
        ;

        foreach ($listeners as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $method = !empty($attribute['method']) ? $attribute['method'] : '__invoke';

                $current_locator
                    ->expects($this->at($locator_index++))
                    ->method('addMethodCall')
                    ->with('registerService', [$attribute['event'], $id, $method])
                ;
            }
        }

        foreach ($subscribers as $id => $attributes) {
            $class_name = $id;

            $subscriber = $this->getMock(Definition::class);
            $subscriber
                ->expects($this->once())
                ->method('getClass')
                ->will($this->returnValue($class_name))
            ;

            $this->container
                ->expects($this->at($container_index++))
                ->method('findDefinition')
                ->with($id)
                ->will($this->returnValue($subscriber))
            ;

            $current_locator
                ->expects($this->at($locator_index++))
                ->method('addMethodCall')
                ->with('registerSubscriberService', [$id, $class_name])
            ;
        }

        $this->pass->process($this->container);
    }
}
