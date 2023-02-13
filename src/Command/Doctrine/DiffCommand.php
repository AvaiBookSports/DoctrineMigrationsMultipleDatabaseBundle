<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends AbstractCommand
{
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
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace to use for the migration (must be in the list of configured namespaces)'
            )
            ->addOption(
                'filter-expression',
                null,
                InputOption::VALUE_REQUIRED,
                'Tables which are filtered by Regular Expression.'
            )
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
            )
            ->addOption(
                'from-empty-schema',
                null,
                InputOption::VALUE_NONE,
                'Generate a full migration as if the current database was empty.'
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = [
            '--formatted' => $input->getOption('formatted'),
            '--line-length' => $input->getOption('line-length'),
            '--check-database-platform' => $input->getOption('check-database-platform'),
            '--allow-empty-diff' => $input->getOption('allow-empty-diff'),
            '--from-empty-schema' => $input->getOption('from-empty-schema'),
        ];

        if ('' !== (string) $input->getOption('namespace')) {
            $parameters['--namespace'] = $input->getOption('namespace');
        }

        if ('' !== (string) $input->getOption('filter-expression')) {
            $parameters['--filter-expression'] = $input->getOption('filter-expression');
        }

        $newInput = new ArrayInput($parameters);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\DiffCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
