<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Command\Doctrine;

use AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();
        $this->configuration = $configuration;
    }

    /**
     * @throws RuntimeException
     *
     * @return DependencyFactory[]
     */
    public function getDependencyFactories(string $entityManager = null): array
    {
        $dependencyFactories = [];

        if (null === $entityManager || '' === $entityManager) {
            $dependencyFactories = $this->configuration->getDependencyFactories();
        } elseif (null !== $this->configuration->getConfigurationByEntityManagerName($entityManager)) {
            $dependencyFactories = [$this->configuration->getConfigurationByEntityManagerName($entityManager)];
        }

        if (0 === count($dependencyFactories)) {
            throw new RuntimeException('No entity manager found');
        }

        return $dependencyFactories;
    }
}
