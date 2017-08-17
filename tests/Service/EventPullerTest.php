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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use GpsLab\Bundle\DomainEvent\Service\EventPuller;
use GpsLab\Domain\Event\Aggregator\AggregateEvents;
use GpsLab\Domain\Event\Event;

class EventPullerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private $em;

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

        $this->em = $this->getMock(EntityManagerInterface::class);
        $this->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;

        $this->puller = new EventPuller($this->em);
    }

    public function testNoEntities()
    {
        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue([]))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue([]))
        ;

        $this->assertEquals([], $this->puller->pull());
    }

    public function testNoEvents()
    {
        $exist_aggregator1 = $this->getMock(AggregateEvents::class);
        $exist_aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue([]))
        ;
        $exist_aggregator2 = $this->getMock(AggregateEvents::class);
        $exist_aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue([]))
        ;

        $removed_aggregator1 = $this->getMock(AggregateEvents::class);
        $removed_aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue([]))
        ;
        $removed_aggregator2 = $this->getMock(AggregateEvents::class);
        $removed_aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue([]))
        ;

        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue([
                [
                    $this->getMock(Proxy::class),
                    $this->getMock(Proxy::class),
                ],
                [
                    new \stdClass(),
                    new \stdClass(),
                ],
                [
                    $exist_aggregator1,
                    $exist_aggregator2,
                ],
            ]))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue([
                $removed_aggregator1,
                $removed_aggregator2
            ]))
        ;

        $this->assertEquals([], $this->puller->pull());
    }

    public function testPull()
    {
        $events = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];

        $slice = round(count($events) / 2);
        $exist_aggregator1 = $this->getMock(AggregateEvents::class);
        $exist_aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, 0, $slice)))
        ;
        $exist_aggregator2 = $this->getMock(AggregateEvents::class);
        $exist_aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, $slice)))
        ;

        $on_remove_events = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];

        $slice = round(count($events) / 2);
        $removed_aggregator1 = $this->getMock(AggregateEvents::class);
        $removed_aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($on_remove_events, 0, $slice)))
        ;
        $removed_aggregator2 = $this->getMock(AggregateEvents::class);
        $removed_aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($on_remove_events, $slice)))
        ;

        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue([
                [
                    $this->getMock(Proxy::class),
                    $this->getMock(Proxy::class),
                ],
                [
                    new \stdClass(),
                    new \stdClass(),
                ],
                [
                    $exist_aggregator1,
                    $exist_aggregator2,
                ],
            ]))
        ;
        $this->uow
            ->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue([
                $removed_aggregator1,
                $removed_aggregator2
            ]))
        ;

        $this->assertEquals(array_merge($on_remove_events, $events), $this->puller->pull());
    }
}
