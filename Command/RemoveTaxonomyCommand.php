<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\DatabaseHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class RemoveTaxonomyCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:remove-taxonomy')
            ->setDescription('Remove a taxonomy')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the taxonomy');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHelper $databaseHelper */
        $databaseHelper = $this->getContainer()->get('startplatz.wordpress_integration.wordpress.database_helper');

        $id = $input->getArgument('id');

        if (!($taxonomy = $databaseHelper->getTaxonomy($id))) {
            $output->writeln("<error>Unknown taxonomy id $id</error>");
            return;
        }

        $output->writeln("Removing taxonomy $id");
        $databaseHelper->removeTaxonomy($id);
    }

}