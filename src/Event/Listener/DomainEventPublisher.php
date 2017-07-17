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
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use GpsLab\Domain\Event\Aggregator\AggregateEvents;
use GpsLab\Domain\Event\Bus\EventBus;

class DomainEventPublisher implements EventSubscriber
{
    /**
     * @var EventBus
     */
    private $bus;

    /**
     * @var bool
     */
    private $enable;

    /**
     * @param EventBus $bus
     * @param bool     $enable
     */
    public function __construct(EventBus $bus, $enable)
    {
        $this->bus = $bus;
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
            Events::postFlush,
        ];
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();

        if ($em->isOpen()) {
            $map = $em->getUnitOfWork()->getIdentityMap();
            $has_events = false;

            foreach ($map as $entities) {
                foreach ($entities as $entity) {
                    // ignore Doctrine proxy classes
                    // proxy class can't have a domain events
                    if ($entity instanceof Proxy || !($entity instanceof AggregateEvents)) {
                        break;
                    }

                    foreach ($entity->pullEvents() as $event) {
                        $this->bus->publish($event);
                        $has_events = true;
                    }
                }
            }

            // flush only if has domain events
            // it necessary for fix recursive handle flush
            if ($has_events) {
                $em->flush();
            }
        }
    }
}
