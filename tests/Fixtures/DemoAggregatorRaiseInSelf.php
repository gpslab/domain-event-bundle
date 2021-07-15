<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Fixtures;

use GpsLab\Bundle\DomainEvent\Event\Aggregator\AbstractAggregateEventsRaiseInSelf;
use Symfony\Contracts\EventDispatcher\Event;

class DemoAggregatorRaiseInSelf extends AbstractAggregateEventsRaiseInSelf
{
    private ?Event $raise_in_self_event = null;

    public function raiseEvent(Event $event): void
    {
        $this->raise($event);
    }

    protected function onPurchaseOrderCreated(Event $event): void
    {
        $this->raise_in_self_event = $event;
    }

    public function getRaiseInSelfEvent(): ?Event
    {
        return $this->raise_in_self_event;
    }
}
