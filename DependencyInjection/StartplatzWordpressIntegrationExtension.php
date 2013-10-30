<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class StartplatzWordpressIntegrationExtension extends Extension {

    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = array();
        array_map(function($config) use (&$configuration) {
            $configuration = array_merge($configuration, $config);
        }, $config);

        foreach ((array)@$configuration['shortcode'] as $name=>$controller)
        {
            $definition = new Definition('Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode\ControllerShortCode',array(new Reference('fragment.handler'),$controller,'%kernel.debug%'));
            $definition->addTag('startplatz.wordpress_integration.shortcode',array('alias'=>$name));
            $container->setDefinition('startplatz.wordpress_integration.configured_shortcode'.$name, $definition);
        }

        $wordpressRootDir = realpath($configuration['wordpress_root_dir']);
        $container->setParameter('startplatz.wordpress_integration.table_prefix', @$configuration['table_prefix'] ?: 'wp_');
        $container->setParameter('startplatz.wordpress_integration.wordpress_root_dir', $wordpressRootDir);
        $container->setParameter('startplatz.wordpress_integration.global_names_cache_file', "$wordpressRootDir/wp-content/globalNames.php");

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $databaseConnection = @$configuration['wordpress_dbal_connection'] ?: 'doctrine.dbal.wordpress_connection';
        $container->getDefinition('startplatz.wordpress_integration.wordpress.database_helper')->replaceArgument(0, new Reference($databaseConnection));
    }

}