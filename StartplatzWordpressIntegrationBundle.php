<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle;

use Startplatz\Bundle\WordpressIntegrationBundle\DependencyInjection\Compiler\ShortCodeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\SymfonyFacade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class StartplatzWordpressIntegrationBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ShortCodeCompilerPass());
    }

    public function boot()
    {
        SymfonyFacade::setContainer($this->container);
    }

}