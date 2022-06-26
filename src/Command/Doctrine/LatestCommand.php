<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LatestCommand extends AbstractCommand
{

    protected function configure(): void
    {
        $this
            ->setName('doctrine:migrations:latest')
            ->setAliases(['latest'])
            ->setDescription('Outputs the latest version')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([]);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\LatestCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
