<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\DatabaseHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ListTaxonomiesCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:list-taxonomies')
            ->setDescription('List wordpress taxonomies')
            ->addArgument('like', InputArgument::OPTIONAL, 'String to search for')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type of taxonomy to search for');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHelper $databaseHelper */
        $databaseHelper = $this->getContainer()->get('startplatz.wordpress_integration.wordpress.database_helper');

        $like = $input->getArgument('like');
        $type = $input->getOption('type');

        if (!($terms = $databaseHelper->listTaxonomies($like, $type))) {
            return;
        }

        $longestSlug = min(50, max(array_map(function($term) {
            return strlen($term['slug']);
        }, $terms)));

        foreach ($terms as $term) {
            $output->writeln(str_replace("\n", '', substr(str_pad($term['slug'], $longestSlug), 0, $longestSlug) . ' (ID: ' . $term['term_taxonomy_id'] . ', Name: ' . substr($term['name'], 0, 50) . ', Type: ' . $term['taxonomy'] . ', Description: '  . substr($term['description'], 0, 50) . ', Count: ' . $term['count'] . ')'));
        }
    }

}