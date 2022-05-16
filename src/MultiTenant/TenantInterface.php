<?php

declare(strict_types=1);

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\MultiTenant;

interface TenantInterface
{
    public function getDatabaseName(): string;
}
