<?php

declare(strict_types=1);

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\MultiTenant;

interface MultiTenantConnectionWrapperInterface
{
    public function selectDatabase(string $dbName): void;
}
