<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Fixtures\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PurchaseOrderCreatedEvent extends Event
{
}
