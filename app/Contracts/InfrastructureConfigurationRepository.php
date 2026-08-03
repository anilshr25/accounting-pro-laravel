<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\InfrastructureConfiguration;

interface InfrastructureConfigurationRepository
{
    public function current(): InfrastructureConfiguration;
}
