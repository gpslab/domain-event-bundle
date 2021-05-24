<?php

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\EventListenerPass;
use GpsLab\Bundle\DomainEvent\DependencyInjection\GpsLabDomainEventExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GpsLabDomainEventBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventListenerPass());
    }

    /**
     * @return GpsLabDomainEventExtension
     */
    public function getContainerExtension()
    {
        if (!($this->extension instanceof GpsLabDomainEventExtension)) {
            $this->extension = new GpsLabDomainEventExtension();
        }

        return $this->extension;
    }
}
