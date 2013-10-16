<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class SymfonyRender implements ShortCode
{

    protected $debug;
    protected $handler;

    public function __construct($debug, FragmentHandler $handler)
    {
        $this->debug = $debug;
        $this->handler = $handler;
    }

    public function execute($attributes, $content = null)
    {
        try {

            $attributes = (array)$attributes;
            $controller = @$attributes['controller'];
            unset($attributes['controller']);
            $renderer = @$attributes['renderer'] ? : 'inline';
            unset($attributes['renderer']);
            $options = $attributes;
            $parameters = @json_decode($content, true) ?: array();

            return $this->handler->render(
                new ControllerReference(
                    $controller,
                    $parameters
                ),
                $renderer,
                $attributes
            );

        } catch (\Exception $e) {

            if ($this->debug) {
                throw $e;
            }
        }
    }

}