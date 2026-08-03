<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Contracts\InfrastructureConfigurationRepository;
use App\Data\InfrastructureConfiguration;

final class MutableInfrastructureConfigurationRepository implements InfrastructureConfigurationRepository
{
    public function __construct(public InfrastructureConfiguration $configuration) {}

    public function current(): InfrastructureConfiguration
    {
        return $this->configuration;
    }
}
