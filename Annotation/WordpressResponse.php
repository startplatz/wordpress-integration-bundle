<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Annotation;

/**
 * @Annotation
 */
class WordpressResponse {

    protected $uri;
    protected $createTwigTemplate = false;

    public function __construct(array $config = array()) {
        foreach ($config as $name => $value) {
            switch ($name) {
                case 'value':
                case 'uri':
                    $this->uri = $value;
                    break;
                case 'createTwigTemplate':
                    $this->createTwigTemplate = $value;
                    break;
                default:
                    throw new \Exception("Invalid parameter $name for WordpressResponse-Annotation");
            }
        }
    }

    public function getUri() {
        return $this->uri;
    }

    public function getCreateTwigTemplate()
    {
        return $this->createTwigTemplate;
    }

}