<?php

declare(strict_types=1);

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase\MultiTenant;

interface MultiTenantRepositoryInterface
{
    /**
     * 
     * @return TenantInterface[] 
     */
    public function getTenants(): array;
}
