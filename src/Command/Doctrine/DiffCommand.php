<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends AbstractCommand
{
    protected static $defaultName = 'doctrine:migrations:diff';

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a migration by comparing your current database to your mapping information.')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command generates a migration by comparing your current database to your mapping information:

    <info>%command.full_name%</info>

EOT
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
            ->addOption(
                'formatted',
                null,
                InputOption::VALUE_NONE,
                'Format the generated SQL.'
            )
            ->addOption(
                'line-length',
                null,
                InputOption::VALUE_REQUIRED,
                'Max line length of unformatted lines.',
                120
            )
            ->addOption(
                'check-database-platform',
                null,
                InputOption::VALUE_OPTIONAL,
                'Check Database Platform to the generated code.',
                false
            )
            ->addOption(
                'allow-empty-diff',
                null,
                InputOption::VALUE_NONE,
                'Do not throw an exception when no changes are detected.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([
            // '--namespace' => $input->getOption('namespace'),
            // '--filter-expression' => $input->getOption('filter-expression'),
            '--formatted' => $input->getOption('formatted'),
            '--line-length' => $input->getOption('line-length'),
            '--check-database-platform' => $input->getOption('check-database-platform'),
            '--allow-empty-diff' => $input->getOption('allow-empty-diff'),
        ]);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\DiffCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
