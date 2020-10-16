# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2]
### Added
- Supported command `doctrine:migrations:current`
- Supported command `doctrine:migrations:latest`
- Supported command `doctrine:migrations:list`
- Supported command `doctrine:migrations:status`
- Supported command `doctrine:migrations:sync-metadata-storage`
- Supported command `doctrine:migrations:up-to-date`

### Changed
- Internal class renaming
- Command description as the original one in `doctrine:migrations:diff` and `doctrine:migrations:version`

## [0.1.1]
### Added
- Project cleanup

## [0.1]
### Added
- Initial support for configuration files
- Initial override of [DoctrineMigrationsBundle](https://github.com/doctrine/DoctrineMigrationsBundle) commands
  - Ability to execute migrations commands to all entity managers, or filtered by the option `--em=default`
  - Supported command `doctrine:migrations:diff`
  - Supported command `doctrine:migrations:migrate`
  - Supported command `doctrine:migrations:version`