<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ShortCodeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('startplatz.wordpress_integration.wordpress_http_kernel')) {
            return;
        }

        $kernel = $container->getDefinition(
            'startplatz.wordpress_integration.wordpress_http_kernel'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'startplatz.wordpress_integration.shortcode'
        );
        foreach ($taggedServices as $id => $allAttributes) {
            foreach ($allAttributes as $attributes) {
                $kernel->addMethodCall(
                    'addShortCode',
                    array(
                        $attributes['alias'],
                        new Reference($id)
                    )
                );
            }
        }
    }
}