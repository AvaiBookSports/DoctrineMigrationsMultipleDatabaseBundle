<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase;

use AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Configuration\Configuration;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Persistence\ManagerRegistry;

class MultipleEntityManagerLoader
{
    /**
     * @var Configuration
     */
    private  $configuration;

    /**
     * @var ManagerRegistry
     */
    private  $registry;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(Configuration $configuration, ManagerRegistry $registry, string $projectDir)
    {
        $this->configuration = $configuration;
        $this->registry = $registry;
        $this->projectDir = $projectDir;
    }

    /**
     * @return DependencyFactory[]
     */
    public function getAllDependencyFactories(): array
    {
        $dependencyFactories = [];

        foreach ($this->configuration->getEntityManagerNames() as $entityManagerName) {
            $dependencyFactories[$entityManagerName] = $this->getDependencyFactory($entityManagerName);
        }

        return $dependencyFactories;
    }

    public function getDependencyFactory(string $entityManagerName): DependencyFactory
    {
        $configurationLoader = new ExistingConfiguration($this->configuration->getConfigurationByEntityManagerName($entityManagerName));
        $entityManagerLoader = new ExistingEntityManager($this->registry->getManager($entityManagerName));
        return DependencyFactory::fromEntityManager($configurationLoader, $entityManagerLoader);
    }
}
