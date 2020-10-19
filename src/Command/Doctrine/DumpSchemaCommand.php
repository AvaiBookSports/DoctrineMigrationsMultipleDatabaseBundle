<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpSchemaCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'doctrine:migrations:dump-schema';

    protected function configure() : void
    {
        $this
            ->setAliases(['dump-schema'])
            ->setDescription('Dump the schema for your database to a migration.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command dumps the schema for your database to a migration:

    <info>%command.full_name%</info>

After dumping your schema to a migration, you can rollup your migrations using the <info>migrations:rollup</info> command.
EOT
            )
            ->addOption(
                'formatted',
                null,
                InputOption::VALUE_NONE,
                'Format the generated SQL.'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Namespace to use for the generated migrations (defaults to the first namespace definition).'
            )
            ->addOption(
                'filter-tables',
                null,
                InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
                'Filter the tables to dump via Regex.'
            )
            ->addOption(
                'line-length',
                null,
                InputOption::VALUE_OPTIONAL,
                'Max line length of unformatted lines.',
                120
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = [
            '--formatted' => $input->getOption('formatted'),
            '--filter-tables' => $input->getOption('filter-tables'),
            '--line-length' => $input->getOption('line-length'),
        ];

        if ($input->getOption('namespace') !== null) {
            $arguments['--namespace'] = $input->getOption('namespace');
        }

        $newInput = new ArrayInput($arguments);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
