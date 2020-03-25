<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Service;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\UnitOfWork;
use GpsLab\Bundle\DomainEvent\Service\EventPuller;
use GpsLab\Domain\Event\Aggregator\AggregateEvents;
use GpsLab\Domain\Event\Event;
use PHPUnit\Framework\TestCase;

class EventPullerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UnitOfWork
     */
    private $uow;

    /**
     * @var EventPuller
     */
    private $puller;

    protected function setUp()
    {
        $this->uow = $this
            ->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->puller = new EventPuller();
    }

    /**
     * @return array
     */
    public function events()
    {
        $events1 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $events2 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $events3 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $events4 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];

        return [
            [[], [], [], []],
            [$events1, [], [], []],
            [[], $events1, [], []],
            [[], [], $events1, []],
            [[], [], [], $events1],
            [$events1, $events2, [], []],
            [$events1, [], $events2, []],
            [$events1, [], [], $events2],
            [[], $events1, [], $events2],
            [[], [], $events1, $events2],
            [$events1, $events2, $events3, []],
            [$events1, $events2, [], $events3],
            [$events1, [], $events2, $events3],
            [[], $events1, $events2, $events3],
            [$events1, $events2, $events3, $events4],
        ];
    }

    /**
     * @dataProvider events
     *
     * @param array $deletions_events
     * @param array $insertions_events
     * @param array $updates_events
     * @param array $map_events
     */
    public function testPull(
        array $deletions_events,
        array $insertions_events,
        array $updates_events,
        array $map_events
    ) {
        if ($map_events) {
            $slice = round(count($map_events) / 2);
            $aggregator1 = $this->getMock(AggregateEvents::class);
            $aggregator1
                ->expects($this->once())
                ->method('pullEvents')
                ->will($this->returnValue(array_slice($map_events, 0, $slice)));
            $aggregator2 = $this->getMock(AggregateEvents::class);
            $aggregator2
                ->expects($this->once())
                ->method('pullEvents')
                ->will($this->returnValue(array_slice($map_events, $slice)));

            $map = [
                [
                    $this->getMock(Proxy::class),
                    $aggregator1,
                ],
                [
                    $aggregator2,
                    new \stdClass(),
                ],
                [
                    new \stdClass(),
                    $this->getMock(Proxy::class),
                ],
            ];
        } else {
            $map = [];
        }

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue($this->getEntitiesFroEvents($deletions_events)))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue($this->getEntitiesFroEvents($insertions_events)))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue($this->getEntitiesFroEvents($updates_events)))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue($map))
        ;

        $expected_events = array_merge(
            $deletions_events,
            $insertions_events,
            $updates_events,
            $map_events
        );

        $this->assertEquals($expected_events, $this->puller->pull($this->uow));
    }

    /**
     * @param Event[] $events
     *
     * @return object[]
     */
    private function getEntitiesFroEvents(array $events)
    {
        if (!$events) {
            return [];
        }

        $slice = round(count($events) / 2);
        $aggregator1 = $this->getMock(AggregateEvents::class);
        $aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, 0, $slice)))
        ;
        $aggregator2 = $this->getMock(AggregateEvents::class);
        $aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, $slice)))
        ;

        return [
            $this->getMock(Proxy::class),
            new \stdClass(),
            $aggregator1,
            $aggregator2,
        ];
    }
}
