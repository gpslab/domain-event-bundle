<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Bundle\DomainEvent\Event\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use GpsLab\Domain\Event\Aggregator\AggregateEventsInterface;
use GpsLab\Domain\Event\Bus\Bus;

class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @var Bus
     */
    private $bus;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @param Bus $bus
     * @param array $events
     */
    public function __construct(Bus $bus, array $events)
    {
        $this->bus = $bus;
        $this->events = array_intersect([
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::preFlush,
        ], $events);
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->events;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->pullAndPublish($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->pullAndPublish($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->pullAndPublish($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preFlush(LifecycleEventArgs $args)
    {
        $this->pullAndPublish($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    protected function pullAndPublish(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof AggregateEventsInterface) {
            $this->bus->pullAndPublish($object);
        }
    }
}
