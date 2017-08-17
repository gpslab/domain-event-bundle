<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use GpsLab\Bundle\DomainEvent\Service\EventPublisher;
use GpsLab\Domain\Event\Bus\EventBus;
use GpsLab\Domain\Event\Event;

class EventPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventBus
     */
    private $bus;

    /**
     * @var EventPublisher
     */
    private $publisher;

    protected function setUp()
    {
        $this->em = $this->getMock(EntityManagerInterface::class);
        $this->bus = $this->getMock(EventBus::class);

        $this->publisher = new EventPublisher($this->em, $this->bus);
    }

    public function testNoEvents()
    {
        $this->bus
            ->expects($this->never())
            ->method('publish')
        ;

        $this->em
            ->expects($this->never())
            ->method('flush')
        ;

        $this->publisher->publish([]);
    }

    /**
     * @return array
     */
    public function totalEvents()
    {
        return [
            [1],
            [5],
        ];
    }

    /**
     * @dataProvider totalEvents
     *
     * @param int $total_events
     */
    public function testHasEvents($total_events)
    {
        $events = [];
        for ($i = 0; $i < $total_events; $i++) {
            $event = $this->getMock(Event::class);
            $events[] = $event;

            $this->bus
                ->expects($this->at($i))
                ->method('publish')
                ->with($event)
            ;
        }

        $this->em
            ->expects($this->once())
            ->method('flush')
        ;

        $this->publisher->publish($events);
    }
}
