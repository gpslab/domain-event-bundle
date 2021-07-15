<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Aggregator;

use GpsLab\Bundle\DomainEvent\Tests\Fixtures\DemoAggregator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class AggregateEventsTest extends TestCase
{
    private DemoAggregator $aggregator;

    protected function setUp(): void
    {
        $this->aggregator = new DemoAggregator();
    }

    public function testRaiseAndPullEvents(): void
    {
        $this->assertEquals([], $this->aggregator->pullEvents());

        $events = [
            new Event(),
            new Event(),
        ];

        foreach ($events as $event) {
            $this->aggregator->raiseEvent($event);
        }

        $this->assertEquals($events, $this->aggregator->pullEvents());
        $this->assertEquals([], $this->aggregator->pullEvents());
    }
}
