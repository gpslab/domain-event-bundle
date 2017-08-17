<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Service;

use Doctrine\ORM\EntityManagerInterface;
use GpsLab\Domain\Event\Bus\EventBus;
use GpsLab\Domain\Event\Event;

class EventPublisher
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventBus
     */
    private $bus;

    /**
     * @param EntityManagerInterface $em
     * @param EventBus               $bus
     */
    public function __construct(EntityManagerInterface $em, EventBus $bus)
    {
        $this->em = $em;
        $this->bus = $bus;
    }

    /**
     * @param Event[] $events
     */
    public function publish(array $events)
    {
        // flush only if has domain events
        // it necessary for fix recursive handle flush
        if ($events) {
            foreach ($events as $event) {
                $this->bus->publish($event);
            }

            $this->em->flush();
        }
    }
}
