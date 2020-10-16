<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncMetadataCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'doctrine:migrations:sync-metadata-storage';

    protected function configure() : void
    {
        parent::configure();

        $this
            ->setAliases(['sync-metadata-storage'])
            ->setDescription('Ensures that the metadata storage is at the latest version.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command updates metadata storage the latest version.

    <info>%command.full_name%</info>
EOT
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([]);

        $newInput->setInteractive($input->isInteractive());

        foreach ($this->getDependencyFactories(strval($input->getOption('em'))) as $dependencyFactory) {
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
        }

        return self::SUCCESS;
    }
}
