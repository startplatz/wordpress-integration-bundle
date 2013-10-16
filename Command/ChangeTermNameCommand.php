<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\DatabaseHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ChangeTermNameCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:change-term-name')
            ->setDescription('Change name of a wordpress term')
            ->addArgument('slug', InputArgument::REQUIRED, 'The slug of the term')
            ->addArgument('name', InputArgument::REQUIRED, 'The new name of the term');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHelper $databaseHelper */
        $databaseHelper = $this->getContainer()->get('startplatz.wordpress_integration.wordpress.database_helper');

        $slug = $input->getArgument('slug');
        $name = $input->getArgument('name');

        if ($term = $databaseHelper->getTermBySlug($slug)) {
            $output->writeln("Change name of term with slug '$slug' to '$name'.");
            $databaseHelper->changeTermName($term['id'], $name);
        } else {
            $output->writeln("<error>There is no term with a slug '$slug'</error>");
        }
    }

}