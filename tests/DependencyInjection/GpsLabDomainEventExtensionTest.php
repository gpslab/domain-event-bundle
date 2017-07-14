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

class GpsLabDomainEventExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private $container_builder;

    /**
     * @var GpsLabDomainEventExtension
     */
    private $extension;

    const CONTAINER_OFFSET = 10;

    protected function setUp()
    {
        $this->container_builder = $this
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
            ],
            [
                [
                    'gpslab_domain_event' => [
                        'bus' => 'queue',
                        'queue' => 'subscribe_executing',
                        'locator' => 'container',
                    ],
                ],
                'domain_event.bus.queue',
                'domain_event.queue.subscribe_executing',
                'domain_event.locator.container',
            ],
            [
                [
                    'gpslab_domain_event' => [
                        'bus' => 'queue',
                        'queue' => 'subscribe_executing',
                        'locator' => 'direct_binding',
                    ],
                ],
                'domain_event.bus.queue',
                'domain_event.queue.subscribe_executing',
                'domain_event.locator.direct_binding',
            ],
            [
                [
                    'gpslab_domain_event' => [
                        'bus' => 'acme.domain.event.bus',
                        'queue' => 'acme.domain.event.queue',
                        'locator' => 'acme.domain.event.locator',
                    ],
                ],
                'acme.domain.event.bus',
                'acme.domain.event.queue',
                'acme.domain.event.locator',
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
     */
    public function testLoad(array $config, $bus, $queue, $locator)
    {
        $this->container_builder
            ->expects($this->at(self::CONTAINER_OFFSET))
            ->method('setAlias')
            ->with('domain_event.bus', $bus)
        ;
        $this->container_builder
            ->expects($this->at(self::CONTAINER_OFFSET + 1))
            ->method('setAlias')
            ->with('domain_event.queue', $queue)
        ;
        $this->container_builder
            ->expects($this->at(self::CONTAINER_OFFSET + 2))
            ->method('setAlias')
            ->with('domain_event.locator', $locator)
        ;

        $this->extension->load($config, $this->container_builder);
    }

    public function testAlias()
    {
        $this->assertEquals('gpslab_domain_event', $this->extension->getAlias());
    }
}
