# DoctrineMigrationsMultipleDatabaseBundle

This bundle extends the [DoctrineMigrationsBundle](https://github.com/doctrine/DoctrineMigrationsBundle) functionality
in a hacky and dirty way to provide a easy way to configure migrations paths for multiple entity managers.

## Configuration

- Install the package by running `composer require avaibooksports/DoctrineMigrationsMultipleDatabaseBundle`
- Go to your `config/bundles.php` file and register the bundle:
```php
AvaiBookSports\Bundle\MigrationsMutlipleDatabase\DoctrineMigrationsMultipleDatabaseBundle::class => ['all' => true],
```
- Finally, create a file in `config/packages/` called `doctrine_migrations_multiple_database.yaml` and follow the next example:
```yaml
doctrine_migrations_multiple_database:
    entity_managers:
        default:
            migrations_paths:
                DoctrineMigrations\Main: '%kernel.project_dir%/migrations/Main'
        geonames:
            migrations_paths:
                DoctrineMigrations\Geonames: '%kernel.project_dir%/migrations/Geonames'
```
- You can leave your `doctirne_migrations.yaml` file untouched. Unmapped commands will fallback to that configuration, and if you need to disable this bundle everything should work as always.

## Usage

Just call the same commands as always, with the same parameters. Right now only the following commands are mapped:

- `doctrine:migrations:diff`
- `doctrine:migrations:migrate`
- `doctrine:migrations:version`

You can run a command for a specific entity manager adding the option `--em=example`

If you call any of the supported commands, they will work as always iterating over all the defined configurations.

### Supported configuration

For now, all configuration parameters should work except `connection` and `em`, because we are already specifying which entity manager we want to connect.

This would be the [example configuration of DoctrineMigrationsBundle](https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html#configuration) translated to this bundle:

```yaml
# config/packages/doctrine_migrations_multiple_database.yaml


doctrine_migrations_multiple_database:
    entity_managers:
        default:
            # List of namespace/path pairs to search for migrations, at least one required
            migrations_paths:
                'App\Migrations': 'src/App'
                'AnotherApp\Migrations': '/path/to/other/migrations'
                'SomeBundle\Migrations': '@SomeBundle/Migrations'

            # List of additional migration classes to be loaded, optional
            migrations:
                - 'App\Migrations\Version123'
                - 'App\Migrations\Version123'

            storage:
                # Default (SQL table) metadata storage configuration
                table_storage:
                    table_name: 'doctrine_migration_versions'
                    version_column_name: 'version'
                    version_column_length: 1024
                    executed_at_column_name: 'executed_at'
                    execution_time_column_name: 'execution_time'

            # Possible values: "BY_YEAR", "BY_YEAR_AND_MONTH", false
            organize_migrations: false

            # Path to your custom migrations template
            custom_template: ~

            # Run all migrations in a transaction.
            all_or_nothing: false

            # Adds an extra check in the generated migrations to ensure that is executed on the same database type.
            check_database_platform: true

            services:
                # Custom migration sorting service id
                'Doctrine\Migrations\Version\Comparator': ~

                # Custom migration classes factory
                'Doctrine\Migrations\Version\MigrationFactory': ~

            factories:
                # Custom migration sorting service id via callables (MyCallableFactory must be a callable)
                'Doctrine\Migrations\Version\Comparator': 'MyCallableFactory'
```

### Supported commands

- `doctrine:migrations:diff`
- `doctrine:migrations:migrate`
- `doctrine:migrations:version`

## Pitfalls

This package is being actively developed to satisfy a very specific scenario in our workflow, but we wanted to share 
this solution with more people struggling with this particular need.

As we are basing our configuration in YAML files, XML and PHP formats are not tested right now. We would love to have 
[feedback](../../issues) from you if you have any problems configuring the bundle. Unit tests should come sooner or later.

Also, we are supporting partially the configuration parameters, and not all commands are mapped.

All releases tagged like `0.x` will be affected by this pitfalls, and release `1.0` will cover a full configuration file 
and all commands.
