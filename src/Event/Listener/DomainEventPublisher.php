<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Event\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use GpsLab\Bundle\DomainEvent\Service\EventPublisher;
use GpsLab\Bundle\DomainEvent\Service\EventPuller;
use GpsLab\Domain\Event\Event;

class DomainEventPublisher implements EventSubscriber
{
    /**
     * @var EventPublisher
     */
    private $publisher;

    /**
     * @var EventPuller
     */
    private $puller;

    /**
     * @var bool
     */
    private $enable;

    /**
     * @var Event[]
     */
    private $events = [];

    /**
     * @param EventPublisher $publisher
     * @param EventPuller    $puller
     * @param bool           $enable
     */
    public function __construct(EventPublisher $publisher, EventPuller $puller, $enable)
    {
        $this->publisher = $publisher;
        $this->puller = $puller;
        $this->enable = $enable;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        if (!$this->enable) {
            return [];
        }

        return [
            Events::preFlush,
            Events::postFlush,
        ];
    }

    public function preFlush()
    {
        // aggregate events from deleted entities
        $this->events = $this->puller->pull();
    }

    public function postFlush()
    {
        $events = array_merge($this->events, $this->puller->pull());

        // clear aggregate events before publish it
        // it necessary for fix recursive publish of events
        $this->events = [];

        $this->publisher->publish($events);
    }
}
