<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Startplatz\Bundle\WordpressIntegrationBundle\Annotation\WordpressResponse;
use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\HttpKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class WordpressResponseListener implements EventSubscriberInterface
{

    protected $wordpressHttpKernel;
    protected $annotationReader;

    public function __construct(HttpKernel $wordpressHttpKernel, Reader $annotationReader)
    {
        $this->wordpressHttpKernel = $wordpressHttpKernel;
        $this->annotationReader = $annotationReader;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {

            $controller = $event->getController();
            $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
            $object    = new \ReflectionClass($className);
            $method    = $object->getMethod($controller[1]);

            $configurations = array_merge(
                $this->getConfigurations($this->annotationReader->getClassAnnotations($object)),
                $this->getConfigurations($this->annotationReader->getMethodAnnotations($method))
            );

            if ($configuration = @$configurations['Startplatz\Bundle\WordpressIntegrationBundle\Annotation\WordpressResponse']) {
                /** @var WordpressResponse $configuration */
                $request = $event->getRequest();

                if ($uri = $configuration->getUri()) {
                    $targetRequest = Request::create(
                        $uri,
                        'GET',
                        array(),
                        iterator_to_array($request->cookies),
                        array(),
                        iterator_to_array($request->server),
                        null
                    );
                } else {
                    $targetRequest = $request;
                }

                $request->attributes->set('_wordpressResponse', $response = $this->wordpressHttpKernel->handle($targetRequest));

                if ($configuration->getCreateTwigTemplate()) {
                    $markup = $response->getContent();

                    $foundBlocks = array();
                    $markup = preg_replace_callback('(%%([^%]+)%%)', function($matches) use (&$foundBlocks, $request) {
                        $name = strtolower($matches[1]);
                        if (isset($foundBlocks[$name])) {
                            return "{{ block('{$name}') }}";
                        } else {
                            $foundBlocks[$name] = true;
                            if ($name == 'canonical') {
                                $blockContent = str_replace(array('http://', 'https://'), '', $request->getUri());
                            } else {
                                $blockContent = '';
                            }
                            return "{% block {$name} %}{$blockContent}{% endblock %}";
                        }
                    }, $markup);

                    $markup = preg_replace('(<meta name="robots" content="([^"]+)"/>)', '<meta name="robots" content="{% block robots %}index,follow{% endblock %}" />', $markup);

                    $markup = str_replace('</head>', '{% block additionalHead %}{% endblock %}</head>', $markup);
                    $markup = str_replace('</body>', '{% block additionalBody %}{% endblock %}</body>', $markup);

                    $request->attributes->set('_wordpressTemplate', $markup);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

    protected function getConfigurations(array $annotations) {
        $configurations = array();
        foreach ($annotations as $configuration) {
            if ($configuration instanceof WordpressResponse) {
                $configurations[get_class($configuration)] = $configuration;
            }
        }
        return $configurations;
    }

}