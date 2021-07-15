<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Event\Aggregator;

abstract class AbstractAggregateEvents implements AggregateEvents
{
    use AggregateEventsTrait;
}
