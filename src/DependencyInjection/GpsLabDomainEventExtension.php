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
        $loader->load('bus.yml');
        $loader->load('locator.yml');
        $loader->load('name_resolver.yml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setAlias('domain_event.locator', $this->getLocatorRealName($config['locator']));
        $container->setAlias('domain_event.name_resolver', $this->getNameResolverRealName($config['name_resolver']));
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
     * @return string
     */
    public function getAlias()
    {
        return 'gpslab_domain_event';
    }
}
