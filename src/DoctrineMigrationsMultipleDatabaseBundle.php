<?php

namespace AvaiBookSports\Bundle\MigrationsMutlipleDatabase;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineMigrationsMultipleDatabaseBundle extends Bundle
{
    public function getParent(): string
    {
        return 'DoctrineMigrationsBundle';
    }
}

