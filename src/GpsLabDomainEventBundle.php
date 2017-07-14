<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\EventListenerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GpsLabDomainEventBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new EventListenerPass());
    }

    /**
     * @return ExtensionInterface|bool
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $extension = $this->createContainerExtension();

            if ($extension instanceof ExtensionInterface) {
                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        return $this->extension;
    }
}
