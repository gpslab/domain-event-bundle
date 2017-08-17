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

        $events = array_merge($events, $this->pullFromEntities($uow->getScheduledEntityDeletions()));
        $events = array_merge($events, $this->pullFromEntities($uow->getScheduledEntityInsertions()));
        $events = array_merge($events, $this->pullFromEntities($uow->getScheduledEntityUpdates()));

        // other entities
        foreach ($uow->getIdentityMap() as $entities) {
            $events = array_merge($events, $this->pullFromEntities($entities));
        }

        return $events;
    }

    /**
     * @param array $entities
     *
     * @return Event[]
     */
    private function pullFromEntities(array $entities)
    {
        $events = [];
        foreach ($entities as $entity) {
            // ignore Doctrine proxy classes
            // proxy class can't have a domain events
            if (!($entity instanceof Proxy) && $entity instanceof AggregateEvents) {
                $events = array_merge($events, $entity->pullEvents());
            }
        }

        return $events;
    }
}
