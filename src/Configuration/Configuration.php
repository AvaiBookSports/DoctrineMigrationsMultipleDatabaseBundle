<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\Configuration;

use Doctrine\Migrations\Configuration\Configuration as DoctrineConfiguration;

class Configuration
{
    /** @var DoctrineConfiguration[] */
    private $entityManagers = [];

    public function addEntityManager(string $name, DoctrineConfiguration $entityManager): self
    {
        $this->entityManagers[$name] = $entityManager;

        return $this;
    }

    /**
     * @return DoctrineConfiguration[]
     */
    public function getEntityManagers(): array
    {
        return $this->entityManagers;
    }

    /**
     * @return string[]
     */
    public function getEntityManagerNames(): array
    {
        return array_keys($this->entityManagers);
    }

    public function getConfigurationByEntityManagerName(string $name): ?DoctrineConfiguration
    {
        if (array_key_exists($name, $this->entityManagers)) {
            return $this->entityManagers[$name];
        }

        return null;
    }
}
