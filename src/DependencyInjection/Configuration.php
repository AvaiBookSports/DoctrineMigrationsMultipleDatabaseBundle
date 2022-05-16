<?php

declare(strict_types=1);

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\DependencyInjection;

use AvaiBookSports\Bundle\MigrationsMutlipleDatabase\MultiTenant\MultiTenantConnectionWrapperInterface;
use AvaiBookSports\Bundle\MigrationsMutlipleDatabase\MultiTenant\MultiTenantRepositoryInterface;
use Doctrine\Bundle\MigrationsBundle\DependencyInjection\Configuration as DoctrineMigrationsConfiguration;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * {@inheritdoc}
 */
class Configuration extends DoctrineMigrationsConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('doctrine_migrations_multiple_database');

        $rootNode = $treeBuilder->getRootNode();

        $organizeMigrationModes = $this->getOrganizeMigrationsModes();

        $rootNode
            ->children()
                ->arrayNode('entity_managers')
                ->arrayPrototype()
                    ->fixXmlConfig('migration', 'migrations')
                    ->fixXmlConfig('migrations_path', 'migrations_paths')
                    ->children()
                        ->arrayNode('migrations_paths')
                            ->info('A list of namespace/path pairs where to look for migrations.')
                            ->defaultValue([])
                            ->useAttributeAsKey('namespace')
                            ->prototype('scalar')->end()
                        ->end()

                        ->arrayNode('services')
                            ->info('A set of services to pass to the underlying doctrine/migrations library, allowing to change its behaviour.')
                            ->useAttributeAsKey('service')
                            ->defaultValue([])
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    return count(array_filter(array_keys($v), static function (string $doctrineService): bool {
                                        return 0 !== strpos($doctrineService, 'Doctrine\Migrations\\');
                                    }));
                                })
                                ->thenInvalid('Valid services for the DoctrineMigrationsBundle must be in the "Doctrine\Migrations" namespace.')
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()

                        ->arrayNode('factories')
                            ->info('A set of callables to pass to the underlying doctrine/migrations library as services, allowing to change its behaviour.')
                            ->useAttributeAsKey('factory')
                            ->defaultValue([])
                            ->validate()
                                ->ifTrue(static function ($v) {
                                    return count(array_filter(array_keys($v), static function (string $doctrineService): bool {
                                        return 0 !== strpos($doctrineService, 'Doctrine\Migrations\\');
                                    }));
                                })
                                ->thenInvalid('Valid callables for the DoctrineMigrationsBundle must be in the "Doctrine\Migrations" namespace.')
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()

                        ->arrayNode('storage')
                            ->addDefaultsIfNotSet()
                            ->info('Storage to use for migration status metadata.')
                            ->children()
                                ->arrayNode('table_storage')
                                    ->addDefaultsIfNotSet()
                                    ->info('The default metadata storage, implemented as a table in the database.')
                                    ->children()
                                        ->scalarNode('table_name')->defaultValue(null)->cannotBeEmpty()->end()
                                        ->scalarNode('version_column_name')->defaultValue(null)->end()
                                        ->scalarNode('version_column_length')->defaultValue(null)->end()
                                        ->scalarNode('executed_at_column_name')->defaultValue(null)->end()
                                        ->scalarNode('execution_time_column_name')->defaultValue(null)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('migrations')
                            ->info('A list of migrations to load in addition to the one discovered via "migrations_paths".')
                            ->prototype('scalar')->end()
                            ->defaultValue([])
                        ->end()
                        ->scalarNode('all_or_nothing')
                            ->info('Run all migrations in a transaction.')
                            ->defaultValue(false)
                        ->end()
                        ->scalarNode('check_database_platform')
                            ->info('Adds an extra check in the generated migrations to allow execution only on the same platform as they were initially generated on.')
                            ->defaultValue(true)
                        ->end()
                        ->scalarNode('custom_template')
                            ->info('Custom template path for generated migration classes.')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('organize_migrations')
                            ->defaultValue(false)
                            ->info('Organize migrations mode. Possible values are: "BY_YEAR", "BY_YEAR_AND_MONTH", false')
                            ->validate()
                                ->ifTrue(static function ($v) use ($organizeMigrationModes): bool {
                                    if (false === $v) {
                                        return false;
                                    }

                                    if (is_string($v) && in_array(strtoupper($v), $organizeMigrationModes, true)) {
                                        return false;
                                    }

                                    return true;
                                })
                                ->thenInvalid('Invalid organize migrations mode value %s')
                            ->end()
                            ->validate()
                                ->ifString()
                                    ->then(static function ($v) {
                                        return constant('Doctrine\Migrations\Configuration\Configuration::VERSIONS_ORGANIZATION_'.strtoupper($v));
                                    })
                            ->end()
                        ->end()
                        ->arrayNode('multitenant')
                            ->addDefaultsIfNotSet()
                            ->info('Enable multitenant support.')
                            ->children()
                                ->scalarNode('wrapper')
                                    ->defaultValue(null)
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->ifTrue(static function ($v): bool {
                                            if (null === $v) {
                                                return true;
                                            } else if (is_string($v) && class_exists($v) && is_subclass_of($v, MultiTenantConnectionWrapperInterface::class)) {
                                                return true;
                                            }
                            
                                            return true;
                                        })
                                        ->thenInvalid('Wrapper class must be null or a valid class implementing ' . MultiTenantConnectionWrapperInterface::class . '.')
                                    ->end()
                                ->end()
                                ->scalarNode('repository')
                                    ->defaultValue(null)
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->ifTrue(static function ($v): bool {
                                            if (null === $v) {
                                                return true;
                                            } else if (is_string($v) && class_exists($v) && is_subclass_of($v, MultiTenantRepositoryInterface::class)) {
                                                return true;
                                            }
                            
                                            return true;
                                        })
                                        ->thenInvalid('Repository class must be null or a valid doctrine repository class implementing ' . MultiTenantRepositoryInterface::class . '.')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Find organize migrations modes for their names.
     *
     * @return string[]
     */
    private function getOrganizeMigrationsModes(): array
    {
        $constPrefix = 'VERSIONS_ORGANIZATION_';
        $prefixLen = strlen($constPrefix);
        $refClass = new ReflectionClass('Doctrine\Migrations\Configuration\Configuration');
        $constsArray = $refClass->getConstants();
        $namesArray = [];

        foreach ($constsArray as $key => $value) {
            if (0 !== strpos($key, $constPrefix)) {
                continue;
            }

            $namesArray[] = substr($key, $prefixLen);
        }

        return $namesArray;
    }
}
