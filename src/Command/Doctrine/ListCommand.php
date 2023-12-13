<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setAliases(['list-migrations'])
            ->setDescription('Display a list of all available migrations and their status.')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command outputs a list of all available migrations and their status:

    <info>%command.full_name%</info>
EOT
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([]);
        $newInput->setInteractive($input->isInteractive());
        $em = (string)$input->getOption('em');

        foreach ($this->getDependencyFactories($em) as $emName => $dependencyFactory) {
            if ('' === $em) {
                $output->writeln(sprintf('<info>EntityManager:</info> %s', $emName));
            }
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\CurrentCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
            $output->writeln('');
        }

        return self::SUCCESS;
    }
}
