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
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];
        $events2 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];
        $events3 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];
        $events4 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
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
     * @param \PHPUnit_Framework_MockObject_MockObject[] $deletions_events
     * @param \PHPUnit_Framework_MockObject_MockObject[] $insertions_events
     * @param \PHPUnit_Framework_MockObject_MockObject[] $updates_events
     * @param \PHPUnit_Framework_MockObject_MockObject[] $map_events
     */
    public function testPull(
        array $deletions_events,
        array $insertions_events,
        array $updates_events,
        array $map_events
    ) {
        if ($map_events) {
            $slice = round(count($map_events) / 2);
            $aggregator1 = $this->getMockBuilder(AggregateEvents::class)->getMock();
            $aggregator1
                ->expects($this->once())
                ->method('pullEvents')
                ->will($this->returnValue(array_slice($map_events, 0, $slice)));
            $aggregator2 = $this->getMockBuilder(AggregateEvents::class)->getMock();
            $aggregator2
                ->expects($this->once())
                ->method('pullEvents')
                ->will($this->returnValue(array_slice($map_events, $slice)));

            $map = [
                [
                    $this->getMockBuilder(Proxy::class)->getMock(),
                    $aggregator1,
                ],
                [
                    $aggregator2,
                    new \stdClass(),
                ],
                [
                    new \stdClass(),
                    $this->getMockBuilder(Proxy::class)->getMock(),
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
        $aggregator1 = $this->getMockBuilder(AggregateEvents::class)->getMock();
        $aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, 0, $slice)))
        ;
        $aggregator2 = $this->getMockBuilder(AggregateEvents::class)->getMock();
        $aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, $slice)))
        ;

        return [
            $this->getMockBuilder(Proxy::class)->getMock(),
            new \stdClass(),
            $aggregator1,
            $aggregator2,
        ];
    }
}
