<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
            $container->setDefinition('startplatz.wordpress_integration.configured_shortcode.'.str_replace('-','_',$name), $definition);
        }

        $wordpressRootDir = realpath($configuration['wordpress_root_dir']);
        $container->setParameter('startplatz.wordpress_integration.wordpress_root_dir', $wordpressRootDir);
        $container->setParameter('startplatz.wordpress_integration.global_names_cache_file', "$wordpressRootDir/wp-content/globalNames.php");

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

    }

}