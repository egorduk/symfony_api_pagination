<?php

namespace Btc\PaginationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class BtcPaginationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $paginatorDef = $container->getDefinition('twig_pagination');
        $paginatorDef->addMethodCall('setFilterTemplate', [$config['template']['filter']]);
        $paginatorDef->addMethodCall('setSortingTemplate', [$config['template']['sorting']]);
        $paginatorDef->addMethodCall('setNavigationTemplate', [$config['template']['navigation']]);
    }
}
