<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

abstract class FragmentHandlerShortCode implements ShortCode {

    protected $fragmentHandler;

    public function __construct(FragmentHandler $fragmentHandler)
    {
        $this->fragmentHandler = $fragmentHandler;
    }

    public function execute($attributes, $content = null)
    {
        return $this->fragmentHandler->render(
            new ControllerReference(
                $this->getController(),
                $this->createParameters($attributes, $content)
            )
        );
    }

    abstract protected function getController();

    protected function createParameters($attributes, $content = null)
    {
        return array();
    }

}