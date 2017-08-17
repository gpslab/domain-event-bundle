<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Service;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use GpsLab\Domain\Event\Aggregator\AggregateEvents;
use GpsLab\Domain\Event\Event;

class EventPuller
{
    /**
     * @param EntityManagerInterface $em
     *
     * @return Event[]
     */
    public function pull(EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();

        $events = [];
        foreach ($uow->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $events = array_merge($events, $this->pullFromEntity($entity));
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $events = array_merge($events, $this->pullFromEntity($entity));
        }

        return $events;
    }

    /**
     * @param object $entity
     *
     * @return Event[]
     */
    private function pullFromEntity($entity)
    {
        // ignore Doctrine proxy classes
        // proxy class can't have a domain events
        if (!($entity instanceof Proxy) && $entity instanceof AggregateEvents) {
            return $entity->pullEvents();
        }

        return [];
    }
}
