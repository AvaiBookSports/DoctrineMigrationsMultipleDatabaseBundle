<?php

declare(strict_types=1);

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\DependencyInjection;

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\DoctrineMigrationsExtension;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use RuntimeException;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineMigrationsMultipleDatabaseExtension extends DoctrineMigrationsExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $locator = new FileLocator(__DIR__ . '/../Resources/config/');
        $loader  = new YamlFileLoader($container, $locator);

        $loader->load('services.yaml');

        foreach ($config['entity_managers'] as $name => $connection) {
            $this->loadEntityManagerConfiguration($name, $connection, $container);
        }
    }

    /**
     * @param  string[][] $connection
     */
    private function loadEntityManagerConfiguration(string $name, array $connection, ContainerBuilder $container): void
    {
        $configuration = $container->setDefinition(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration', $name), new ChildDefinition('doctrine.migrations_multiple_database.connection_configuration'));
        $container->register(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration_loader', $name), ExistingConfiguration::class);

        $container
            ->register(sprintf('doctrine.migrations_multiple_database.%s_em_loader', $name), Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager::class)
            ->addArgument(new Reference(sprintf('doctrine.orm.%s_entity_manager', $name)));

        $diDefinition = $container->setDefinition(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.dependency_factory', $name), new ChildDefinition('doctrine.migrations_multiple_database.dependency_factory'));
        $diDefinition
            ->setFactory([DependencyFactory::class, 'fromEntityManager'])
            ->setArgument(0, new Reference(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration_loader', $name)))
            ->setArgument(1, new Reference(sprintf('doctrine.migrations_multiple_database.%s_em_loader', $name)));

        foreach ($connection['migrations_paths'] as $migrationNamespace => $migrationPath) {
            $migrationDirectory = $this->checkIfBundleRelativePath($migrationPath, $container);
            $configuration->addMethodCall('addMigrationsDirectory', [$migrationNamespace, $migrationDirectory]);
        }

        foreach ($connection['migrations'] as $migrationClass) {
            $configuration->addMethodCall('addMigrationClass', [$migrationClass]);
        }

        if ($connection['organize_migrations'] !== false) {
            $configuration->addMethodCall('setMigrationOrganization', [$connection['organize_migrations']]);
        }

        if ($connection['custom_template'] !== null) {
            $configuration->addMethodCall('setCustomTemplate', [$connection['custom_template']]);
        }

        $configuration->addMethodCall('setAllOrNothing', [$connection['all_or_nothing']]);
        $configuration->addMethodCall('setCheckDatabasePlatform', [$connection['check_database_platform']]);

        $container
            ->getDefinition('doctrine.migrations_multiple_database.configuration')
            ->addMethodCall('addEntityManager', [$name, new Reference(sprintf('doctrine.migrations_multiple_database.%s_entity_manager.configuration', $name))]);

        foreach ($connection['services'] as $doctrineId => $symfonyId) {
            $diDefinition->addMethodCall('setDefinition', [$doctrineId, new ServiceClosureArgument(new Reference($symfonyId))]);
        }

        foreach ($connection['factories'] as $doctrineId => $symfonyId) {
            $diDefinition->addMethodCall('setDefinition', [$doctrineId, new Reference($symfonyId)]);
        }

        if (! isset($connection['services'][MetadataStorage::class])) {
            $storageConfiguration = $connection['storage']['table_storage'];

            $storageDefinition = new Definition(TableMetadataStorageConfiguration::class);
            $container->setDefinition(sprintf('doctrine.migrations_multiple_database.storage.%s_table_storage', $name), $storageDefinition);
            $container->setAlias('doctrine.migrations_multiple_database.storage.%s_metadata_storage', 'doctrine.migrations_multiple_database.storage.%s_table_storage');

            if ($storageConfiguration['table_name']!== null) {
                $storageDefinition->addMethodCall('setTableName', [$storageConfiguration['table_name']]);
            }
            if ($storageConfiguration['version_column_name']!== null) {
                $storageDefinition->addMethodCall('setVersionColumnName', [$storageConfiguration['version_column_name']]);
            }
            if ($storageConfiguration['version_column_length']!== null) {
                $storageDefinition->addMethodCall('setVersionColumnLength', [$storageConfiguration['version_column_length']]);
            }
            if ($storageConfiguration['executed_at_column_name']!== null) {
                $storageDefinition->addMethodCall('setExecutedAtColumnName', [$storageConfiguration['executed_at_column_name']]);
            }
            if ($storageConfiguration['execution_time_column_name']!== null) {
                $storageDefinition->addMethodCall('setExecutionTimeColumnName', [$storageConfiguration['execution_time_column_name']]);
            }

            $configuration->addMethodCall('setMetadataStorageConfiguration', [new Reference(sprintf('doctrine.migrations_multiple_database.storage.%s_table_storage', $name))]);
        }
    }

    private function checkIfBundleRelativePath(string $path, ContainerBuilder $container) : string
    {
        if (isset($path[0]) && $path[0] === '@') {
            $pathParts  = explode('/', $path);
            $bundleName = substr($pathParts[0], 1);

            $bundlePath = $this->getBundlePath($bundleName, $container);
            return $bundlePath . substr($path, strlen('@' . $bundleName));
        }

        return $path;
    }

    private function getBundlePath(string $bundleName, ContainerBuilder $container) : string
    {
        $bundleMetadata = $container->getParameter('kernel.bundles_metadata');

        if (! isset($bundleMetadata[$bundleName])) {
            throw new RuntimeException(sprintf(
                'The bundle "%s" has not been registered, available bundles: %s',
                $bundleName,
                implode(', ', array_keys($bundleMetadata))
            ));
        }

        return $bundleMetadata[$bundleName]['path'];
    }
}
