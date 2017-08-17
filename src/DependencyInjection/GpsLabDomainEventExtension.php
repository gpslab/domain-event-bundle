<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GpsLabDomainEventExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('queue.yml');
        $loader->load('bus.yml');
        $loader->load('locator.yml');
        $loader->load('publisher.yml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setAlias('domain_event.bus', $this->busRealName($config['bus']));
        $container->setAlias('domain_event.queue', $this->queueRealName($config['queue']));
        $container->setAlias('domain_event.locator', $this->locatorRealName($config['locator']));

        $container->getDefinition('domain_event.publisher')->replaceArgument(2, $config['publish_on_flush']);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function busRealName($name)
    {
        if (in_array($name, ['listener_located', 'queue'])) {
            return 'domain_event.bus.'.$name;
        }

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function queueRealName($name)
    {
        if (in_array($name, ['pull_memory', 'subscribe_executing'])) {
            return 'domain_event.queue.'.$name;
        }

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function locatorRealName($name)
    {
        if (in_array($name, ['direct_binding', 'container', 'symfony'])) {
            return 'domain_event.locator.'.$name;
        }

        return $name;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'gpslab_domain_event';
    }
}
