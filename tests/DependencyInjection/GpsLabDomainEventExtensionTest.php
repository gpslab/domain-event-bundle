<?php

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\DependencyInjection;

use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use GpsLab\Domain\Event\Bus\EventBus;
use GpsLab\Domain\Event\Listener\Subscriber;
use GpsLab\Domain\Event\Queue\EventQueue;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GpsLabDomainEventExtensionTest extends TestCase
{
    /**
     * @var GpsLabDomainEventExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->extension = new GpsLabDomainEventExtension();
        $this->container = new ContainerBuilder();
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
     * @param array  $config
     * @param string $bus
     * @param string $queue
     * @param string $locator
     * @param bool   $publish_on_flush
     */
    public function testLoad(array $config, $bus, $queue, $locator, $publish_on_flush)
    {
        $this->extension->load($config, $this->container);

        $this->assertEquals($bus, $this->container->getAlias('domain_event.bus'));
        $this->assertEquals($queue, $this->container->getAlias('domain_event.queue'));
        $this->assertEquals($locator, $this->container->getAlias('domain_event.locator'));
        $this->assertEquals($bus, $this->container->getAlias(EventBus::class));
        $this->assertEquals($queue, $this->container->getAlias(EventQueue::class));

        $publisher = $this->container->getDefinition('domain_event.publisher');
        $this->assertEquals($publish_on_flush, $publisher->getArgument(2));

        if (method_exists($this->container, 'registerForAutoconfiguration')) {
            $has_subscriber = false;
            foreach ($this->container->getAutoconfiguredInstanceof() as $key => $definition) {
                if ($key === Subscriber::class) {
                    $has_subscriber = true;
                    $this->assertTrue($definition->hasTag('domain_event.subscriber'));
                    $this->assertTrue($definition->isAutowired());
                }
            }
            $this->assertTrue($has_subscriber);
        }
    }

    public function testAlias()
    {
        $this->assertEquals('gpslab_domain_event', $this->extension->getAlias());
    }
}
