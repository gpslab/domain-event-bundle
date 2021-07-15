<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event;

use Doctrine\ORM\UnitOfWork;
use GpsLab\Bundle\DomainEvent\Event\Aggregator\AggregateEvents;
use GpsLab\Bundle\DomainEvent\Event\Puller;
use GpsLab\Bundle\DomainEvent\Tests\Fixtures\SimpleObject;
use GpsLab\Bundle\DomainEvent\Tests\Fixtures\SimpleObjectProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

class PullerTest extends TestCase
{
    /**
     * @var MockObject&UnitOfWork
     */
    private UnitOfWork $uow;

    private Puller $puller;

    protected function setUp(): void
    {
        $this->uow = $this
            ->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->puller = new Puller();
    }

    /**
     * @return Event[][][]
     */
    public function provideEvents(): array
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
     * @dataProvider provideEvents
     *
     * @param Event[] $deletions_events
     * @param Event[] $insertions_events
     * @param Event[] $updates_events
     * @param Event[] $map_events
     */
    public function testPull(
        array $deletions_events,
        array $insertions_events,
        array $updates_events,
        array $map_events
    ): void {
        if ($map_events) {
            $slice = (int) round(count($map_events) / 2);

            $aggregator1 = $this->getMockBuilder(AggregateEvents::class)->getMock();
            $aggregator1
                ->expects($this->once())
                ->method('pullEvents')
                ->willReturn(array_slice($map_events, 0, $slice))
            ;
            $aggregator2 = $this->getMockBuilder(AggregateEvents::class)->getMock();
            $aggregator2
                ->expects($this->once())
                ->method('pullEvents')
                ->willReturn(array_slice($map_events, $slice))
            ;

            $map = [
                [
                    $this->getMockBuilder(SimpleObjectProxy::class)->getMock(),
                    $aggregator1,
                ],
                [
                    $aggregator2,
                    new SimpleObject(),
                ],
                [
                    new SimpleObject(),
                    $this->getMockBuilder(SimpleObjectProxy::class)->getMock(),
                ],
            ];
        } else {
            $map = [];
        }

        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($this->getEntitiesFroEvents($deletions_events))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($this->getEntitiesFroEvents($insertions_events))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($this->getEntitiesFroEvents($updates_events))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->willReturn($map)
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
    private function getEntitiesFroEvents(array $events): array
    {
        if (!$events) {
            return [];
        }

        $slice = (int) round(count($events) / 2);

        $aggregator1 = $this->getMockBuilder(AggregateEvents::class)->getMock();
        $aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->willReturn(array_slice($events, 0, $slice))
        ;
        $aggregator2 = $this->getMockBuilder(AggregateEvents::class)->getMock();
        $aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->willReturn(array_slice($events, $slice))
        ;

        return [
            $this->getMockBuilder(SimpleObjectProxy::class)->getMock(),
            new SimpleObject(),
            $aggregator1,
            $aggregator2,
        ];
    }
}
