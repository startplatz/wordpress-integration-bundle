<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Annotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class WordpressResponse {

    protected $uri;
    protected $createTwigTemplate = false;

    public function __construct($config = array(), ?bool $createTwigTemplate = null) {
        if (is_string($config)) {
            $this->uri = $config;
            $config = array();
        }

        if ($config === null) {
            $config = array();
        }

        if (!is_array($config)) {
            throw new \InvalidArgumentException('WordpressResponse expects an array, string, or null configuration.');
        }

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

        if ($createTwigTemplate !== null) {
            $this->createTwigTemplate = $createTwigTemplate;
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
