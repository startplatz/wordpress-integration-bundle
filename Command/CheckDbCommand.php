<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDbCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('startplatz:wordpress-integration:check-db')
            ->setDescription('Checks, if Database allready exists')
            ->addArgument('database', InputArgument::REQUIRED, 'Database name');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get('database_connection');

        $db = $input->getArgument('database');

        $query = $connection->executeQuery("SHOW DATABASES LIKE :dbname",array('dbname'=>$db));
        $result = $query->fetchColumn();
        if($result == $db)
            $output->writeln("Database exists");
        else
            $output->writeln("Database exists not");
    }

}