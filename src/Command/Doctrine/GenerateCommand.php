<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setAliases(['generate'])
            ->setDescription('Generate a blank migration class.')
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace to use for the migration (must be in the list of configured namespaces)'
            )
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command generates a blank migration class:

    <info>%command.full_name%</info>

EOT
            )
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Name of the Entity Manager to handle.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parameters = [];

        if ('' !== (string) $input->getOption('namespace')) {
            $parameters['--namespace'] = $input->getOption('namespace');
        }

        $newInput = new ArrayInput($parameters);
        $newInput->setInteractive($input->isInteractive());
        $em = (string)$input->getOption('em');

        foreach ($this->getDependencyFactories($em) as $emName => $dependencyFactory) {
            if ('' === $em) {
                $output->writeln(sprintf('<info>EntityManager:</info> %s', $emName));
            }
            $otherCommand = new \Doctrine\Migrations\Tools\Console\Command\GenerateCommand($dependencyFactory);
            $otherCommand->run($newInput, $output);
            $output->writeln('');
        }

        return self::SUCCESS;
    }
}
