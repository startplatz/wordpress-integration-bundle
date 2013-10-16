<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\DatabaseHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MergeTaxonomiesCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:merge-taxonomies')
            ->setDescription('Merge one taxonomy to another')
            ->addArgument('sourceId', InputArgument::REQUIRED, 'The id of the source taxonomy')
            ->addArgument('destinationId', InputArgument::REQUIRED, 'The id of the destination taxonomy');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHelper $databaseHelper */
        $databaseHelper = $this->getContainer()->get('startplatz.wordpress_integration.wordpress.database_helper');

        $sourceId = $input->getArgument('sourceId');
        $destinationId = $input->getArgument('destinationId');

        if (!($source = $databaseHelper->getTaxonomy($sourceId))) {
            $output->writeln("<error>Unknown taxonomy id $sourceId</error>");
            return;
        }

        if (!($destination = $databaseHelper->getTaxonomy($destinationId))) {
            $output->writeln("<error>Unknown taxonomy id $destinationId</error>");
            return;
        }

        if ($source['taxonomy'] != $destination['taxonomy']) {
            $output->writeln("<error>Cannot copy from taxonomy type {$source['taxonomy']} to type {$destination['taxonomy']}");
            return;
        }

        $output->writeln("Merging taxonomy $sourceId to $destinationId");
        $databaseHelper->mergeTaxonomies($sourceId, $destinationId);
    }

}