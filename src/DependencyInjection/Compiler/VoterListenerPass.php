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
use Symfony\Component\DependencyInjection\Reference;

class VoterListenerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('domain_event.locator.voter')) {
            return;
        }

        $current_locator = $container->findDefinition('domain_event.locator');
        $voter_locator = $container->findDefinition('domain_event.locator.voter');

        // register services only if current locator is voter locator
        if ($voter_locator === $current_locator) {
            foreach ($container->findTaggedServiceIds('domain_event.listener') as  $id => $attributes) {
                $voter_locator->addMethodCall('register', [new Reference($id)]);
            }
        }

        // BC: get services from old tag
        foreach ($container->findTaggedServiceIds('domain_event.voter_listener') as  $id => $attributes) {
            $voter_locator->addMethodCall('register', [new Reference($id)]);
        }
    }
}
