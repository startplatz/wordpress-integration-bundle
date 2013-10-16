<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class SymfonyFacade
{

    static protected $container;

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function service($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return self::$container->get($id, $invalidBehavior);
    }

    public static function parameter($name)
    {
        return self::$container->getParameter($name);
    }

    public static function render($controller, array $parameters = array()) {
        return self::$container->get('fragment.handler')->render(new ControllerReference($controller, $parameters));
    }

}