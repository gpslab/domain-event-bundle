<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Listener;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use GpsLab\Bundle\DomainEvent\Event\Listener\DomainEventPublisher;
use GpsLab\Domain\Event\Aggregator\AggregateEvents;
use GpsLab\Domain\Event\Bus\EventBus;
use GpsLab\Domain\Event\Event;

class DomainEventPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventBus
     */
    private $bus;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PostFlushEventArgs
     */
    private $args;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    private $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UnitOfWork
     */
    private $uow;
    /**
     * @var DomainEventPublisher
     */
    private $publisher;

    protected function setUp()
    {
        $this->bus = $this->getMock(EventBus::class);
        $this->args = $this
            ->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->em = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->uow = $this
            ->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->publisher = new DomainEventPublisher($this->bus, true);
    }

    public function testSubscribedEventsDisabled()
    {
        $publisher = new DomainEventPublisher($this->bus, false);
        $this->assertEquals([], $publisher->getSubscribedEvents());
    }

    public function testSubscribedEventsEnabled()
    {
        $publisher = new DomainEventPublisher($this->bus, true);
        $this->assertEquals([Events::postFlush], $publisher->getSubscribedEvents());
    }

    public function testPostFlushNotOpen()
    {
        $this->args
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em))
        ;

        $this->em
            ->expects($this->once())
            ->method('isOpen')
            ->will($this->returnValue(false))
        ;
        $this->em
            ->expects($this->never())
            ->method('getUnitOfWork')
        ;
        $this->em
            ->expects($this->never())
            ->method('flush')
        ;

        $this->bus
            ->expects($this->never())
            ->method('publish')
        ;
        $this->bus
            ->expects($this->never())
            ->method('pullAndPublish')
        ;

        $this->publisher->postFlush($this->args);
    }

    public function testPostFlushNotEvents()
    {
        $map = [];

        $this->args
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em))
        ;

        $this->em
            ->expects($this->once())
            ->method('isOpen')
            ->will($this->returnValue(true))
        ;
        $this->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;
        $this->em
            ->expects($this->never())
            ->method('flush')
        ;

        $this->bus
            ->expects($this->never())
            ->method('publish')
        ;
        $this->bus
            ->expects($this->never())
            ->method('pullAndPublish')
        ;

        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue($map));
        ;

        $this->publisher->postFlush($this->args);
    }

    public function testPostFlushNotDomainEvents()
    {
        $aggregator1 = $this->getMock(AggregateEvents::class);
        $aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue([]))
        ;
        $aggregator2 = $this->getMock(AggregateEvents::class);
        $aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue([]))
        ;

        $map = [
            [
                $this->getMock(Proxy::class),
                $this->getMock(Proxy::class),
            ],
            [
                new \stdClass(),
                new \stdClass(),
            ],
            [
                $aggregator1,
                $aggregator2
            ],
        ];

        $this->args
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em))
        ;

        $this->em
            ->expects($this->once())
            ->method('isOpen')
            ->will($this->returnValue(true))
        ;
        $this->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;
        $this->em
            ->expects($this->never())
            ->method('flush')
        ;

        $this->bus
            ->expects($this->never())
            ->method('publish')
        ;
        $this->bus
            ->expects($this->never())
            ->method('pullAndPublish')
        ;

        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue($map));
        ;

        $this->publisher->postFlush($this->args);
    }

    public function testPostFlushPublishDomainEvents()
    {
        $events = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];

        $aggregator1 = $this->getMock(AggregateEvents::class);
        $aggregator1
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, 0, round(count($events) / 2))))
        ;
        $aggregator2 = $this->getMock(AggregateEvents::class);
        $aggregator2
            ->expects($this->once())
            ->method('pullEvents')
            ->will($this->returnValue(array_slice($events, round(count($events) / 2))))
        ;

        $map = [
            [
                $aggregator1,
                $aggregator2
            ],
        ];

        $this->args
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em))
        ;

        $this->em
            ->expects($this->once())
            ->method('isOpen')
            ->will($this->returnValue(true))
        ;
        $this->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;
        $this->em
            ->expects($this->once())
            ->method('flush')
        ;

        foreach ($events as $i => $event) {
            $this->bus
                ->expects($this->at($i))
                ->method('publish')
                ->with($event)
            ;
        }
        $this->bus
            ->expects($this->never())
            ->method('pullAndPublish')
        ;

        $this->uow
            ->expects($this->once())
            ->method('getIdentityMap')
            ->will($this->returnValue($map));
        ;

        $this->publisher->postFlush($this->args);
    }
}
