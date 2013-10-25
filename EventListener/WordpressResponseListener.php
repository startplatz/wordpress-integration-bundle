<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Startplatz\Bundle\WordpressIntegrationBundle\Annotation\WordpressResponse;
use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\HttpKernel;
use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class WordpressResponseListener implements EventSubscriberInterface
{

    protected $wordpressHttpKernel;
    protected $annotationReader;
    protected $shortCodes = array();

    public function __construct(HttpKernel $wordpressHttpKernel, Reader $annotationReader)
    {
        $this->wordpressHttpKernel = $wordpressHttpKernel;
        $this->annotationReader = $annotationReader;
    }

    public function addShortCode($name, ShortCode $shortCode) {
        $this->shortCodes[$name] = $shortCode;
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

                $response->setContent(
                    $this->expandShortCodes($response->getContent())
                );

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

    protected function expandShortCodes($content) {
        preg_match('(\<body[^>]+\>(.*)\</body\>)s', $content, $matches);

        $body = $matches[1];


        /*
         *  ACHTUNG! DIESER CODE IST ZU GROSSEN TEILEN AUS WORDPRESS KOPIERT!
         *
         */
        $tagnames = array_keys($this->shortCodes);
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

        $pattern =
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '\\b'                              // Word boundary
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]

        $shortCodes = $this->shortCodes;
        $newBody = preg_replace_callback("/$pattern/s", function($m) use ($shortCodes) {
                // allow [[foo]] syntax for escaping a tag
                if ( $m[1] == '[' && $m[6] == ']' ) {
                    return substr($m[0], 1, -1);
                }

                $tag = $m[2];
                $attr = shortcode_parse_atts( $m[3] );

                if ( isset( $m[5] ) ) {
                    // enclosing tag - extra parameter
                    return $m[1] . call_user_func( array($shortCodes[$tag], 'execute'), $attr, $m[5], $tag ) . $m[6];
                } else {
                    // self-closing tag
                    return $m[1] . call_user_func( array($shortCodes[$tag], 'execute'), $attr, null,  $tag ) . $m[6];
                }
        }, $body);

        return str_replace($body, $newBody, $content);
    }

}