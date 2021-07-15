<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Event\Aggregator;

use Symfony\Contracts\EventDispatcher\Event;

trait AggregateEventsRaiseInSelfTrait
{
    /**
     * @var Event[]
     */
    private array $events = [];

    private function raiseInSelf(Event $event): void
    {
        $method = $this->eventHandlerName($event);

        // if method is not exists is not a critical error
        if (method_exists($this, $method)) {
            $this->{$method}($event);
        }
    }

    protected function raise(Event $event): void
    {
        $this->events[] = $event;
        $this->raiseInSelf($event);
    }

    /**
     * @return Event[]
     */
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    /**
     * Get handler method name from event.
     *
     * Override this method if you want to change algorithm to generate the handler method name.
     */
    protected function eventHandlerName(Event $event): string
    {
        $class = get_class($event);

        if ('Event' === substr($class, -5)) {
            $class = substr($class, 0, -5);
        }

        $class = str_replace('_', '\\', $class); // convert names for classes not in namespace
        $parts = explode('\\', $class);

        return 'on'.end($parts);
    }
}
