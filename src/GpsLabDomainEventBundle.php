<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent;

use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GpsLabDomainEventBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (!$this->extension instanceof GpsLabDomainEventExtension) {
            $this->extension = new GpsLabDomainEventExtension();
        }

        return $this->extension;
    }
}
