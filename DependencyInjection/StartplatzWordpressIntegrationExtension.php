<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class StartplatzWordpressIntegrationExtension extends Extension {

    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = array();
        array_map(function($config) use (&$configuration) {
            $configuration = array_merge($configuration, $config);
        }, $config);

        $wordpressRootDir = realpath($configuration['wordpress_root_dir']);
        $container->setParameter('startplatz.wordpress_integration.table_prefix', @$configuration['table_prefix'] ?: 'wp_');
        $container->setParameter('startplatz.wordpress_integration.wordpress_root_dir', $wordpressRootDir);
        $container->setParameter('startplatz.wordpress_integration.global_names_cache_file', "$wordpressRootDir/wp-content/globalNames.php");

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

}