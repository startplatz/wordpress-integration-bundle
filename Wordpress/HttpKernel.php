<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\ShortCode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpKernel implements HttpKernelInterface
{

    protected $wordpressRootDir;
    protected $wordpressGlobalNamesCacheFile;
    protected $catch;
    protected $outputBufferLevel;
    protected $oldGlobals;

    public function __construct($wordpressRootDir, $wordpressGlobalNamesCacheFile)
    {
        $this->wordpressRootDir = $wordpressRootDir;
        $this->wordpressGlobalNamesCacheFile = $wordpressGlobalNamesCacheFile;
    }

    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if ($type !== self::MASTER_REQUEST) {
            throw new \LogicException('Wordpress\HttpKernel cannot handle SUB_REQUESTS');
        }
        unset($type);

        $this->catch = $catch;
        unset($catch);

        $this->startOutputBuffer();

        try {

            $wp_the_query = null;

            $this->storeGlobals();

            $request->overrideGlobals();

            if ($globalNames = @include($this->wordpressGlobalNamesCacheFile)) {
                foreach ($globalNames ? : array() as $name) {
                    @eval('global $' . $name . ';');
                }
            } else {
                throw new \RuntimeException('The global names cache file has to be generated with "app/console startplatz:wordpress-integration:build-global-names-cache"');
            }

            define('WP_USE_THEMES', true);
            $time_start = microtime(true);

            require_once("{$this->wordpressRootDir}/wp-load.php");

            global $wp_query;
            $wp_query = $wp_the_query;

            \wp();

            require_once("{$this->wordpressRootDir}/wp-includes/template-loader.php");

            $content = $this->endOutputBuffer();
            $statusCode = is_404() ? 404 : 200;
            $headers = $this->flushHeaders();

            $this->restoreGlobals();

            return new Response($content, $statusCode, $headers);

        } catch (\Exception $e) {
            $this->endOutputBuffer();
            $this->flushHeaders();
            $this->restoreGlobals();

            if ($this->catch) {
                return new Response($e->getMessage(), 500);
            } else {
                throw $e;
            }
        }
    }

    protected function startOutputBuffer() {
        ob_start();
        $this->outputBufferLevel = ob_get_level();
    }

    protected function endOutputBuffer() {
        while (ob_get_level() > $this->outputBufferLevel) {
            ob_end_flush();
        }
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    protected function flushHeaders() {
        $headers = array();
        foreach (headers_list() as $header) {
            if (preg_match('(^([^:]+):\s+(.*)$)', $header, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        header_remove();

        return $headers;
    }

    protected function storeGlobals() {
        $this->oldGlobals = array();
        foreach ($GLOBALS as $name => $value) {
            $this->oldGlobals[$name] = $value;
        }
        unset($this->oldGlobals['_SESSION']);
        unset($this->oldGlobals['GLOBALS']);
        unset($this->oldGlobals['HTTP_SESSION_VARS']);
        unset($this->oldGlobals['_COOKIE']);
        unset($this->oldGlobals['HTTP_COOKIE_VARS']);
    }

    protected function restoreGlobals() {
        foreach ($GLOBALS as $name => $value) {
            if (!in_array($name, array('_SESSION', 'GLOBALS', 'HTTP_SESSION_VARS', '_COOKIE', 'HTTP_COOKIE_VARS'))) {
                if (!array_key_exists($name, $this->oldGlobals)) {
                    unset($GLOBALS[$name]);
                }
            }
        }
        foreach ($this->oldGlobals as $name => $value) {
            $GLOBALS[$name] = $value;
        }
    }

}