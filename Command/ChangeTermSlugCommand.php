<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Startplatz\Bundle\WordpressIntegrationBundle\Wordpress\DatabaseHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ChangeTermSlugCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:change-term-slug')
            ->setDescription('Change slug of a wordpress term')
            ->addArgument('slug', InputArgument::REQUIRED, 'The slug of the term')
            ->addArgument('newSlug', InputArgument::REQUIRED, 'The new slug of the term');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHelper $databaseHelper */
        $databaseHelper = $this->getContainer()->get('startplatz.wordpress_integration.wordpress.database_helper');

        $slug = $input->getArgument('slug');
        $newSlug = $input->getArgument('newSlug');

        if ($term = $databaseHelper->getTermBySlug($slug)) {
            if (!$databaseHelper->getTermBySlug($newSlug)) {

                $output->writeln("Changing slug of term with slug '$slug' to '$newSlug'");
                $databaseHelper->changeTermSlug($term['id'], $newSlug);

            } else {

                $output->writeln("<error>There is already a term with the slug '$newSlug'");

            }
        } else {
            $output->writeln("<error>There is no term with a slug '$slug'</error>");
        }
    }

}