<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Fixtures;

use GpsLab\Bundle\DomainEvent\Event\Aggregator\AbstractAggregateEvents;
use Symfony\Contracts\EventDispatcher\Event;

class DemoAggregator extends AbstractAggregateEvents
{
    public function raiseEvent(Event $event): void
    {
        $this->raise($event);
    }
}
