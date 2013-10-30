<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class ControllerShortCode implements ShortCode
{

    protected $controller;
    protected $handler;
    protected $debug;

    public function __construct(FragmentHandler $handler, $controller,$debug)
    {
        $this->controller = $controller;
        $this->handler = $handler;
        $this->debug = $debug;
    }

    public function execute($attributes, $content = null)
    {
        try {

            $attributes = (array)$attributes;
            return $this->handler->render(
                new ControllerReference(
                    $this->controller,
                    $attributes
                )
            );

        } catch (\Exception $e) {

            if ($this->debug) {
                throw $e;
            }
        }
    }

}