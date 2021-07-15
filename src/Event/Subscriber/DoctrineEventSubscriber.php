<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use GpsLab\Bundle\DomainEvent\Event\Publisher;

class DoctrineEventSubscriber implements EventSubscriber
{
    private Publisher $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        // aggregate events from deleted entities
        $this->publisher->aggregateEvents($args->getEntityManager());
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        // aggregate PreRemove/PostRemove events
        $this->publisher->aggregateEvents($args->getEntityManager());

        $this->publisher->dispatchEvents($args->getEntityManager());
    }
}
