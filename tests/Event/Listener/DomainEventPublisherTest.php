<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use GpsLab\Bundle\DomainEvent\Event\Listener\DomainEventPublisher;
use GpsLab\Bundle\DomainEvent\Service\EventPuller;
use GpsLab\Domain\Event\Bus\EventBus;
use GpsLab\Domain\Event\Event;

class DomainEventPublisherTest extends \PHPUnit_Framework_TestCase
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
     * @var OnFlushEventArgs
     */
    private $on_flush;

    /**
     * @var PostFlushEventArgs
     */
    private $post_flush;

    /**
     * @var DomainEventPublisher
     */
    private $publisher;

    protected function setUp()
    {
        $this->bus = $this->getMock(EventBus::class);
        $this->puller = $this->getMock(EventPuller::class);
        $this->em = $this->getMock(EntityManagerInterface::class);
        $this->on_flush = new OnFlushEventArgs($this->em);
        $this->post_flush = new PostFlushEventArgs($this->em);

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
        $this->puller
            ->expects($this->once())
            ->method('pull')
            ->with($this->em)
        ;

        $this->publisher->onFlush($this->on_flush);
    }

    /**
     * @return array
     */
    public function events()
    {
        $remove_events = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $exist_events = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];

        return [
            [[], [], []],
            [$remove_events, [], $remove_events],
            [[], $exist_events, $exist_events],
            [$remove_events, $exist_events, array_merge($remove_events, $exist_events)],
        ];
    }

    /**
     * @dataProvider events
     *
     * @param array $remove_events
     * @param array $exist_events
     * @param array $expected_events
     */
    public function testPublishEvents(array $remove_events, array $exist_events, array $expected_events)
    {
        $this->puller
            ->expects($this->at(0))
            ->method('pull')
            ->with($this->em)
            ->will($this->returnValue($remove_events))
        ;
        $this->puller
            ->expects($this->at(1))
            ->method('pull')
            ->with($this->em)
            ->will($this->returnValue($exist_events))
        ;

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

        $this->publisher->onFlush($this->on_flush);
        $this->publisher->postFlush($this->post_flush);
    }

    public function testRecursivePublish()
    {
        $remove_events1 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $remove_events2 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $exist_events1 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];
        $exist_events2 = [
            $this->getMock(Event::class),
            $this->getMock(Event::class),
            $this->getMock(Event::class),
        ];

        $this->puller
            ->expects($this->at(0))
            ->method('pull')
            ->with($this->em)
            ->will($this->returnValue($remove_events1))
        ;
        $this->puller
            ->expects($this->at(1))
            ->method('pull')
            ->with($this->em)
            ->will($this->returnValue($exist_events1))
        ;
        $this->puller
            ->expects($this->at(2))
            ->method('pull')
            ->with($this->em)
            ->will($this->returnValue($remove_events2))
        ;
        $this->puller
            ->expects($this->at(3))
            ->method('pull')
            ->with($this->em)
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

        $this->em
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $this->publisher->onFlush($this->on_flush);
        $this->publisher->postFlush($this->post_flush);
        // recursive call
        $this->publisher->onFlush($this->on_flush);
        $this->publisher->postFlush($this->post_flush);
    }
}
