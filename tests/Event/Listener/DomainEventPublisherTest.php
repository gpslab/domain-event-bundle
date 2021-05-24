<?php

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use GpsLab\Bundle\DomainEvent\Event\Listener\DomainEventPublisher;
use GpsLab\Bundle\DomainEvent\Service\EventPuller;
use GpsLab\Domain\Event\Bus\EventBus;
use GpsLab\Domain\Event\Event;
use PHPUnit\Framework\TestCase;

class DomainEventPublisherTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventBus
     */
    private $bus;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventPuller
     */
    private $puller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UnitOfWork
     */
    private $uow;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OnFlushEventArgs
     */
    private $on_flush;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PostFlushEventArgs
     */
    private $post_flush;

    /**
     * @var DomainEventPublisher
     */
    private $publisher;

    protected function setUp()
    {
        $this->bus = $this->getMockBuilder(EventBus::class)->getMock();
        $this->puller = $this->getMockBuilder(EventPuller::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $this->on_flush = $this
            ->getMockBuilder(OnFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->on_flush
            ->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em))
        ;

        $this->post_flush = $this
            ->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->post_flush
            ->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em))
        ;

        $this->uow = $this
            ->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->publisher = new DomainEventPublisher($this->puller, $this->bus, true);
    }

    public function testDisabled()
    {
        $publisher = new DomainEventPublisher($this->puller, $this->bus, false);
        $this->assertEquals([], $publisher->getSubscribedEvents());
    }

    public function testEnabled()
    {
        $publisher = new DomainEventPublisher($this->puller, $this->bus, true);
        $this->assertEquals([Events::onFlush, Events::postFlush], $publisher->getSubscribedEvents());
    }

    public function testPreFlush()
    {
        $this->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;

        $this->puller
            ->expects($this->once())
            ->method('pull')
            ->with($this->uow)
        ;

        $this->publisher->onFlush($this->on_flush);
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
        ];
        $events2 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];

        return [
            [[], [], []],
            [$events1, []],
            [[], $events2],
            [$events1, $events2],
        ];
    }

    /**
     * @dataProvider events
     *
     * @param array $remove_events
     * @param array $exist_events
     */
    public function testPublishEvents(array $remove_events, array $exist_events)
    {
        $this->puller
            ->expects($this->at(0))
            ->method('pull')
            ->with($this->uow)
            ->will($this->returnValue($remove_events))
        ;
        $this->puller
            ->expects($this->at(1))
            ->method('pull')
            ->with($this->uow)
            ->will($this->returnValue($exist_events))
        ;

        $expected_events = array_merge($remove_events, $exist_events);

        if ($expected_events) {
            foreach ($expected_events as $i => $expected_event) {
                $this->bus
                    ->expects($this->at($i))
                    ->method('publish')
                    ->with($expected_event)
                ;
            }
            $this->em
                ->expects($this->once())
                ->method('flush')
            ;
        } else {
            $this->bus
                ->expects($this->never())
                ->method('publish')
            ;
            $this->em
                ->expects($this->never())
                ->method('flush')
            ;
        }
        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;

        $this->publisher->onFlush($this->on_flush);
        $this->publisher->postFlush($this->post_flush);
    }

    public function testRecursivePublish()
    {
        $remove_events1 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];
        $remove_events2 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];
        $exist_events1 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];
        $exist_events2 = [
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
            $this->getMockBuilder(Event::class)->getMock(),
        ];

        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow))
        ;
        $this->em
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $this->puller
            ->expects($this->at(0))
            ->method('pull')
            ->with($this->uow)
            ->will($this->returnValue($remove_events1))
        ;
        $this->puller
            ->expects($this->at(1))
            ->method('pull')
            ->with($this->uow)
            ->will($this->returnValue($exist_events1))
        ;
        $this->puller
            ->expects($this->at(2))
            ->method('pull')
            ->with($this->uow)
            ->will($this->returnValue($remove_events2))
        ;
        $this->puller
            ->expects($this->at(3))
            ->method('pull')
            ->with($this->uow)
            ->will($this->returnValue($exist_events2))
        ;

        $expected_events = array_merge($remove_events1, $exist_events1, $remove_events2, $exist_events2);
        foreach ($expected_events as $i => $expected_event) {
            $this->bus
                ->expects($this->at($i))
                ->method('publish')
                ->with($expected_event)
            ;
        }

        $this->publisher->onFlush($this->on_flush);
        $this->publisher->postFlush($this->post_flush);
        // recursive call
        $this->publisher->onFlush($this->on_flush);
        $this->publisher->postFlush($this->post_flush);
    }
}
