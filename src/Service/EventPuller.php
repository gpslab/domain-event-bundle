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
use Doctrine\ORM\UnitOfWork;
use GpsLab\Domain\Event\Aggregator\AggregateEvents;
use GpsLab\Domain\Event\Event;

class EventPuller
{
    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->uow = $em->getUnitOfWork();
    }

    /**
     * @return Event[]
     */
    public function pull()
    {
        $events = [];
        foreach ($this->uow->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $events = array_merge($events, $this->pullFromEntity($entity));
            }
        }

        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
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
