<?php

declare(strict_types=1);

namespace App\Repositories\Infrastructure;

use App\Contracts\InfrastructureConfigurationRepository;
use App\Data\InfrastructureConfiguration;
use App\Exceptions\InfrastructureConfigurationException;
use App\Models\Tenant\SiteSetting\SiteSetting;

final class EloquentInfrastructureConfigurationRepository implements InfrastructureConfigurationRepository
{
    public function current(): InfrastructureConfiguration
    {
        $setting = SiteSetting::query()->first();

        if ($setting === null) {
            throw new InfrastructureConfigurationException('Infrastructure settings are not configured for the current context.');
        }

        $tenant = tenancy()->initialized ? tenant() : null;
        $scopeId = $tenant === null ? 'central' : (string) $tenant->getTenantKey();

        return new InfrastructureConfiguration(
            scopeId: $scopeId,
            isTenant: $tenant !== null,
            mailDriver: $this->nullableString($setting->mail_driver),
            mailHost: $this->nullableString($setting->mail_host),
            mailPort: is_numeric($setting->mail_port) ? (int) $setting->mail_port : null,
            mailUsername: $this->nullableString($setting->mail_user_name),
            mailPassword: $this->nullableString($setting->mail_password),
            mailEncryption: $this->nullableString($setting->mail_encryption),
            mailSenderName: $this->nullableString($setting->mail_sender_name),
            mailSenderAddress: $this->nullableString($setting->mail_sender_address),
            mailFallbackHost: $this->nullableString($setting->mail_fallback_host),
            mailFallbackPort: is_numeric($setting->mail_fallback_port) ? (int) $setting->mail_fallback_port : null,
            mailFallbackUsername: $this->nullableString($setting->mail_fallback_user_name),
            mailFallbackPassword: $this->nullableString($setting->mail_fallback_password),
            mailFallbackEncryption: $this->nullableString($setting->mail_fallback_encryption),
            storageType: $this->nullableString($setting->storage_type) ?? 'local',
            storageAccessKey: $this->nullableString($setting->storage_access_key),
            storageSecretKey: $this->nullableString($setting->storage_secret_key),
            storageRegion: $this->nullableString($setting->storage_region),
            storageEndpoint: $this->nullableString($setting->storage_endpoint),
            storageBucket: $this->nullableString($setting->storage_bucket_name),
            storageUrl: $this->nullableString($setting->storage_url),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
