<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\DependencyInjection;

use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class GpsLabDomainEventExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private $container;

    /**
     * @var GpsLabDomainEventExtension
     */
    private $extension;

    const CONTAINER_OFFSET = 12;

    protected function setUp()
    {
        $this->container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->extension = new GpsLabDomainEventExtension();
    }

    /**
     * @return array
     */
    public function config()
    {
        return [
            [
                [],
                'domain_event.bus.listener_located',
                'domain_event.queue.pull_memory',
                'domain_event.locator.symfony',
                false,
            ],
            [
                [
                    'gpslab_domain_event' => [
                        'bus' => 'queue',
                        'queue' => 'subscribe_executing',
                        'locator' => 'container',
                        'publish_on_flush' => false,
                    ],
                ],
                'domain_event.bus.queue',
                'domain_event.queue.subscribe_executing',
                'domain_event.locator.container',
                false,
            ],
            [
                [
                    'gpslab_domain_event' => [
                        'bus' => 'queue',
                        'queue' => 'subscribe_executing',
                        'locator' => 'direct_binding',
                        'publish_on_flush' => true,
                    ],
                ],
                'domain_event.bus.queue',
                'domain_event.queue.subscribe_executing',
                'domain_event.locator.direct_binding',
                true,
            ],
            [
                [
                    'gpslab_domain_event' => [
                        'bus' => 'acme.domain.event.bus',
                        'queue' => 'acme.domain.event.queue',
                        'locator' => 'acme.domain.event.locator',
                        'publish_on_flush' => true,
                    ],
                ],
                'acme.domain.event.bus',
                'acme.domain.event.queue',
                'acme.domain.event.locator',
                true,
            ],
        ];
    }

    /**
     * @dataProvider config
     *
     * @param array $config
     * @param string $bus
     * @param string $queue
     * @param string $locator
     * @param bool   $publish_on_flush
     */
    public function testLoad(array $config, $bus, $queue, $locator, $publish_on_flush)
    {
        $publisher = $this->getMock(Definition::class);
        $publisher
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(2, $publish_on_flush)
        ;

        $this->container
            ->expects($this->at(self::CONTAINER_OFFSET))
            ->method('setAlias')
            ->with('domain_event.bus', $bus)
        ;
        $this->container
            ->expects($this->at(self::CONTAINER_OFFSET + 1))
            ->method('setAlias')
            ->with('domain_event.queue', $queue)
        ;
        $this->container
            ->expects($this->at(self::CONTAINER_OFFSET + 2))
            ->method('setAlias')
            ->with('domain_event.locator', $locator)
        ;
        $this->container
            ->expects($this->at(self::CONTAINER_OFFSET + 3))
            ->method('getDefinition')
            ->with('domain_event.publisher')
            ->will($this->returnValue($publisher))
        ;

        $this->extension->load($config, $this->container);
    }

    public function testAlias()
    {
        $this->assertEquals('gpslab_domain_event', $this->extension->getAlias());
    }
}
