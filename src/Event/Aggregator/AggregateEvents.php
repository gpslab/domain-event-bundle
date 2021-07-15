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

interface AggregateEvents
{
    /**
     * @return Event[]
     */
    public function pullEvents(): array;
}
