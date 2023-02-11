<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpToDateCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setAliases(['up-to-date'])
            ->setDescription('Tells you if your schema is up-to-date.')
            ->addOption('fail-on-unregistered', 'u', InputOption::VALUE_NONE, 'Whether to fail when there are unregistered extra migrations found')
            ->addOption('list-migrations', 'l', InputOption::VALUE_NONE, 'Show a list of missing or not migrated versions.')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command tells you if your schema is up-to-date:

    <info>%command.full_name%</info>
EOT
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([
            '--fail-on-unregistered' => $input->getOption('fail-on-unregistered'),
            '--list-migrations' => $input->getOption('list-migrations'),
        ]);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\UpToDateCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
