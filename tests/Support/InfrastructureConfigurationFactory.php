<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Data\InfrastructureConfiguration;

final class InfrastructureConfigurationFactory
{
    /** @param array<string, mixed> $overrides */
    public static function tenant(string $tenantId, array $overrides = []): InfrastructureConfiguration
    {
        return new InfrastructureConfiguration(...array_merge([
            'scopeId' => $tenantId,
            'isTenant' => true,
            'mailDriver' => 'smtp',
            'mailHost' => "smtp.{$tenantId}.test",
            'mailPort' => 587,
            'mailUsername' => "user-{$tenantId}",
            'mailPassword' => "password-{$tenantId}",
            'mailEncryption' => 'tls',
            'mailSenderName' => "Tenant {$tenantId}",
            'mailSenderAddress' => "mail@{$tenantId}.test",
            'mailFallbackHost' => null,
            'mailFallbackPort' => null,
            'mailFallbackUsername' => null,
            'mailFallbackPassword' => null,
            'mailFallbackEncryption' => null,
            'storageType' => 'local',
            'storageAccessKey' => null,
            'storageSecretKey' => null,
            'storageRegion' => null,
            'storageEndpoint' => null,
            'storageBucket' => null,
            'storageUrl' => null,
        ], $overrides));
    }
}
