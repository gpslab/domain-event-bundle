<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2016, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Bundle\DomainEvent\DependencyInjection;

use Doctrine\ORM\Events;
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
        $loader->load('bus.yml');
        $loader->load('locator.yml');
        $loader->load('name_resolver.yml');
        $loader->load('subscriber.yml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $config = $this->mergeDefaultConfig((array) $config);

        $container->setAlias('domain_event.locator', $this->getLocatorRealName($config['locator']));
        $container->setAlias('domain_event.name_resolver', $config['name_resolver']);

        $container->setParameter('domain_event.doctrine.handle_events', $config['doctrine']['handle_events']);
        $container->setParameter('domain_event.doctrine.connections', $config['doctrine']['connections']);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getLocatorRealName($name)
    {
        if (in_array($name, ['voter', 'named_event'])) {
            return 'domain_event.locator.'.$name;
        }

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getNameResolverRealName($name)
    {
        if (in_array($name, ['event_class', 'event_class_last_part', 'named_event'])) {
            return 'domain_event.name_resolver.'.$name;
        }

        return $name;
    }

    /**
     * Merge default config.
     *
     * gpslab_domain_event:
     *     locator: 'named_event'
     *     name_resolver: 'event_class'
     *     doctrine:
     *         handle_events:
     *             - 'preFlush'
     *         connections:
     *             - 'default'
     *
     * @param array $config
     *
     * @return array
     */
    protected function mergeDefaultConfig(array $config)
    {
        $config = array_merge([
            'locator' => 'named_event',
            'name_resolver' => 'event_class',
            'doctrine' => [],
        ], $config);

        $config['doctrine'] = array_merge([
            'handle_events' => [],
            'connections' => [],
        ], (array) $config['doctrine']);

        $config['doctrine']['handle_events'] = array_merge([
            Events::preFlush,
        ], (array) $config['doctrine'],['handle_events']);

        $config['doctrine']['connections'] = array_merge([
            'default',
        ], (array) $config['doctrine'],['connections']);

        return $config;
    }
}
