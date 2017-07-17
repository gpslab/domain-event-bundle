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
use GpsLab\Domain\Event\Bus\EventBus;

class DomainEventPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventBus
     */
    private $bus;

    /**
     * @var DomainEventPublisher
     */
    private $publisher;

    protected function setUp()
    {
        $this->bus = $this->getMock(EventBus::class);
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
}
