<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Aggregator;

use GpsLab\Bundle\DomainEvent\Tests\Fixtures\DemoAggregatorRaiseInSelf;
use GpsLab\Bundle\DomainEvent\Tests\Fixtures\Event\PurchaseOrderCreatedEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class AggregateEventsRaiseInSelfTest extends TestCase
{
    private DemoAggregatorRaiseInSelf $aggregator;

    protected function setUp(): void
    {
        $this->aggregator = new DemoAggregatorRaiseInSelf();
    }

    public function testRaiseAndPullEvents()
    {
        $this->assertEquals([], $this->aggregator->pullEvents());

        $events = [
            new Event(),
            new Event(),
        ];

        foreach ($events as $event) {
            $this->aggregator->raiseEvent($event);
            $this->assertNull($this->aggregator->getRaiseInSelfEvent());
        }

        $this->assertEquals($events, $this->aggregator->pullEvents());
        $this->assertEquals([], $this->aggregator->pullEvents());
    }

    public function testRaiseInSel(): void
    {
        $this->assertEquals([], $this->aggregator->pullEvents());

        $event1 = new PurchaseOrderCreatedEvent();
        $event2 = new \Acme_Demo_PurchaseOrderCreated();

        $this->aggregator->raiseEvent($event1);
        $this->assertEquals($event1, $this->aggregator->getRaiseInSelfEvent());

        $this->aggregator->raiseEvent($event2);
        $this->assertEquals($event2, $this->aggregator->getRaiseInSelfEvent());

        $this->assertEquals([$event1, $event2], $this->aggregator->pullEvents());
        $this->assertEquals([], $this->aggregator->pullEvents());
    }
}
