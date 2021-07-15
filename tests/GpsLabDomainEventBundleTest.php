<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests;

use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use GpsLab\Bundle\DomainEvent\GpsLabDomainEventBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GpsLabDomainEventBundleTest extends TestCase
{
    private GpsLabDomainEventBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new GpsLabDomainEventBundle();
    }

    public function testCorrectBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testContainerExtension(): void
    {
        $this->assertInstanceOf(GpsLabDomainEventExtension::class, $this->bundle->getContainerExtension());
    }
}
