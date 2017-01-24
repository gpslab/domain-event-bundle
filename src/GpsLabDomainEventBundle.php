<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Bundle\DomainEvent;

use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\NamedEventListenerPass;
use GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler\VoterListenerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GpsLabDomainEventBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new NamedEventListenerPass());
        $container->addCompilerPass(new VoterListenerPass());
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
