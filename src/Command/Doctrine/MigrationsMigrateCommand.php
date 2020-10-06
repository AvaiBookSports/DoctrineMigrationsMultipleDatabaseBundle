<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use AvaiBookSports\Bundle\MigrationsMutlipleDatabase\MultipleEntityManagerLoader;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Doctrine migrations' commands are final classes. That's why we cannot extend and override them.
 */
class MigrationsMigrateCommand extends Command
{
    /**
     * @var MultipleEntityManagerLoader
     */
    private $multipleEntityManagerLoader;

    public function __construct(MultipleEntityManagerLoader $multipleEntityManagerLoader)
    {
        parent::__construct();
        $this->multipleEntityManagerLoader = $multipleEntityManagerLoader;
    }

    protected static $defaultName = 'doctrine:migrations:migrate';

    protected function configure(): void
    {
        $this
            ->setDescription('Proxy to launch doctrine:migrations:migrate command as it would require a "configuration" option, and we can\'t define em/connection in config.')
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
        if ($input->getOption('em') === null ) {
            $dependencyFactories = $this->multipleEntityManagerLoader->getAllDependencyFactories();
        } elseif (is_string($input->getOption('em'))) {
            $dependencyFactories = [$this->multipleEntityManagerLoader->getDependencyFactory($input->getOption('em'))];
        } else {
            throw new RuntimeException('Invalid value for "em" option');
        }

        $newInput = new ArrayInput([
            'version' => $input->getArgument('version'),
            '--write-sql' => $input->getOption('write-sql'),
            '--dry-run' => $input->getOption('dry-run'),
            '--query-time' => $input->getOption('query-time'),
            '--allow-no-migration' => $input->getOption('allow-no-migration'),
            '--all-or-nothing' => $input->getOption('all-or-nothing'),
        ]);

        $newInput->setInteractive($input->isInteractive());

        foreach ($dependencyFactories as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\MigrateCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return Command::SUCCESS;
    }
}
