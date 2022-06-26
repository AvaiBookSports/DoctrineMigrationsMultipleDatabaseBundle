<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends AbstractCommand
{

    protected function configure(): void
    {
        $this
            ->setName('doctrine:migrations:migrate')
            ->setDescription('Execute a migration to a specified version or the latest available version.')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'The version FQCN or alias (first, prev, next, latest) to migrate to.',
                'latest'
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
            ->addOption(
                'write-sql',
                null,
                InputOption::VALUE_OPTIONAL,
                'The path to output the migration SQL file instead of executing it. Defaults to current working directory.',
                false
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Execute the migration as a dry run.'
            )
            ->addOption(
                'query-time',
                null,
                InputOption::VALUE_NONE,
                'Time all the queries individually.'
            )
            ->addOption(
                'allow-no-migration',
                null,
                InputOption::VALUE_NONE,
                'Do not throw an exception if no migration is available.'
            )
            ->addOption(
                'all-or-nothing',
                null,
                InputOption::VALUE_OPTIONAL,
                'Wrap the entire migration in a transaction.',
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([
            'version' => $input->getArgument('version'),
            '--write-sql' => $input->getOption('write-sql'),
            '--dry-run' => $input->getOption('dry-run'),
            '--query-time' => $input->getOption('query-time'),
            '--allow-no-migration' => $input->getOption('allow-no-migration'),
            '--all-or-nothing' => $input->getOption('all-or-nothing'),
        ]);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\MigrateCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
