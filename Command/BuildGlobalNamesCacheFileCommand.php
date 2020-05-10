<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BuildGlobalNamesCacheFileCommand extends Command {
    protected static $defaultName = 'startplatz:wordpress-integration:build-global-names-cache';

    protected $wordpressRootDir;
    protected $cacheFile;

    public function __construct( string $wordpressRootDir, string $cacheFile) {
        parent::__construct();
        $this->wordpressRootDir = $wordpressRootDir;
        $this->cacheFile = $cacheFile;

    }

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:build-global-names-cache')
            ->setDescription('Generate the list of global variable names for wordpress integration');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $previousNames = @include($this->cacheFile);

        $names = $previousNames ?: array();

        $finder = new Finder();
        $finder
            ->files()
            ->in($this->wordpressRootDir)
            ->name('*.php');

        foreach ($finder as $file) {
            if (preg_match_all('(\$([\w\_]+))', file_get_contents($file), $matches)) {
                $names = array_merge($names, $matches[1]);
            }
        }

        $names = array_unique($names);
        $names = array_filter($names, function($name) {
            $isValid = true;
            $isValid = ($isValid && !preg_match('(^\d)', $name));
            $isValid = ($isValid && !in_array($name, array(
                'this',
                'GLOBALS',
                '_SERVER',
                '_GET',
                '_POST',
                '_FILES',
                '_COOKIE',
                '_SESSION',
                '_REQUEST',
                '_ENV',
                'HTTP_SERVER_VARS',
                'HTTP_GET_VARS',
                'HTTP_POST_VARS',
                'HTTP_POST_FILES',
                'HTTP_COOKIE_VARS',
                'HTTP_SESSION_VARS',
                'HTTP_ENV_VARS',
                'php_errormsg',
                'HTTP_RAW_POST_DATA',
                'http_response_header',
                'argc',
                'argv'
            )));
            return $isValid;
        });

        sort($names);

        file_put_contents($this->cacheFile, '<?php return ' . str_replace(array("\n", ' '), '', var_export($names, true)) . ';');
    }

}