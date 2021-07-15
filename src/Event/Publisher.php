<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Event;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class Publisher
{
    private Puller $puller;
    private EventDispatcher $dispatcher;

    /**
     * @var Event[][]
     */
    private array $events = [];

    public function __construct(Puller $puller, EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->puller = $puller;
    }

    public function aggregateEvents(EntityManagerInterface $em): void
    {
        $emid = spl_object_id($em);

        $this->events[$emid] = array_merge($this->events[$emid] ?? [], $this->puller->pull($em->getUnitOfWork()));
    }

    public function dispatchEvents(EntityManagerInterface $em): void
    {
        $emid = spl_object_id($em);

        if (!isset($this->events[$emid])) {
            return; // no events for dispatch
        }

        // clear aggregate events before publish it
        // it necessary for fix recursive publish of events
        $events = $this->events[$emid];
        unset($this->events[$emid]);

        // flush only if has domain events
        // it necessary for fix recursive handle flush
        if ($events !== []) {
            foreach ($events as $event) {
                $this->dispatcher->dispatch($event);
            }

            $em->flush();
        }
    }
}
