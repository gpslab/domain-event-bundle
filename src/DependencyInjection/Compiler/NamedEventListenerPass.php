<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Bundle\DomainEvent\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NamedEventListenerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('domain_event.locator.named_event')) {
            return;
        }

        $definition = $container->findDefinition('domain_event.locator.named_event');
        foreach ($container->findTaggedServiceIds('domain_event.named_event_listener') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $definition->addMethodCall('registerService', [$attribute['event'], $id]);
            }
        }
    }
}
