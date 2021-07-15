<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\DependencyInjection;

use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use GpsLab\Bundle\DomainEvent\Event\Publisher;
use GpsLab\Bundle\DomainEvent\Event\Puller;
use GpsLab\Bundle\DomainEvent\Event\Subscriber\DoctrineEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GpsLabDomainEventExtensionTest extends TestCase
{
    private GpsLabDomainEventExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new GpsLabDomainEventExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->hasDefinition(Puller::class));
        $this->assertTrue($this->container->hasDefinition(Publisher::class));
        $this->assertTrue($this->container->hasDefinition(DoctrineEventSubscriber::class));
    }

    public function testAlias(): void
    {
        $this->assertEquals('gpslab_domain_event', $this->extension->getAlias());
    }
}
