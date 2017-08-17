<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Listener;

use Doctrine\ORM\Events;
use GpsLab\Bundle\DomainEvent\Event\Listener\DomainEventPublisher;
use GpsLab\Bundle\DomainEvent\Service\EventPublisher;
use GpsLab\Bundle\DomainEvent\Service\EventPuller;
use GpsLab\Domain\Event\Event;

class DomainEventPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventPublisher
     */
    private $event_publisher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventPuller
     */
    private $event_puller;
    /**
     * @var DomainEventPublisher
     */
    private $publisher;

    protected function setUp()
    {
        $this->event_publisher = $this
            ->getMockBuilder(EventPublisher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->event_puller = $this
            ->getMockBuilder(EventPuller::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->publisher = new DomainEventPublisher($this->event_publisher, $this->event_puller, true);
    }

    public function testDisabled()
    {
        $publisher = new DomainEventPublisher($this->event_publisher, $this->event_puller, false);
        $this->assertEquals([], $publisher->getSubscribedEvents());
    }

    public function testEnabled()
    {
        $publisher = new DomainEventPublisher($this->event_publisher, $this->event_puller, true);
        $this->assertEquals([Events::preFlush, Events::postFlush], $publisher->getSubscribedEvents());
    }

    public function testPreFlush()
    {
        $this->event_puller
            ->expects($this->once())
            ->method('pull')
        ;

        $this->publisher->preFlush();
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
        $this->event_puller
            ->expects($this->at(0))
            ->method('pull')
            ->will($this->returnValue($remove_events))
        ;
        $this->event_puller
            ->expects($this->at(1))
            ->method('pull')
            ->will($this->returnValue($exist_events))
        ;

        $this->event_publisher
            ->expects($this->once())
            ->method('publish')
            ->with($expected_events)
        ;

        $this->publisher->preFlush();
        $this->publisher->postFlush();
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

        $this->event_puller
            ->expects($this->at(0))
            ->method('pull')
            ->will($this->returnValue($remove_events1))
        ;
        $this->event_puller
            ->expects($this->at(1))
            ->method('pull')
            ->will($this->returnValue($exist_events1))
        ;
        $this->event_puller
            ->expects($this->at(2))
            ->method('pull')
            ->will($this->returnValue($remove_events2))
        ;
        $this->event_puller
            ->expects($this->at(3))
            ->method('pull')
            ->will($this->returnValue($exist_events2))
        ;

        $this->event_publisher
            ->expects($this->at(0))
            ->method('publish')
            ->with(array_merge($remove_events1, $exist_events1))
        ;
        $this->event_publisher
            ->expects($this->at(1))
            ->method('publish')
            ->with(array_merge($remove_events2, $exist_events2))
        ;

        $this->publisher->preFlush();
        $this->publisher->postFlush();
        // recursive call
        $this->publisher->preFlush();
        $this->publisher->postFlush();
    }
}
