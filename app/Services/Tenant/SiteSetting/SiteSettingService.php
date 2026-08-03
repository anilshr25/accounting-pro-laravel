<?php

namespace App\Services\Tenant\SiteSetting;

use App\Http\Resources\Tenant\SiteSetting\SiteSettingResource;
use App\Models\Tenant\SiteSetting\SiteSetting;
use Illuminate\Support\Facades\Log;
use Throwable;

class SiteSettingService
{
    protected $site_setting;

    public function __construct(SiteSetting $site_setting)
    {
        $this->site_setting = $site_setting;
    }

    public function get()
    {
        $setting = $this->site_setting->first();

        return $setting ? new SiteSettingResource($setting) : null;
    }

    public function storeOrUpdate(array $data): SiteSetting
    {
        try {
            $setting = $this->site_setting->first();
            if ($setting) {
                $data = $this->preserveWriteOnlySecrets($setting, $data);
                $setting->update($data);

                return $setting;
            }

            return $this->site_setting->create($data);
        } catch (Throwable $exception) {
            Log::error('Tenant infrastructure settings update failed.', [
                'tenant_id' => tenancy()->initialized ? tenant()->getTenantKey() : 'central',
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    private function preserveWriteOnlySecrets(SiteSetting $setting, array $data): array
    {
        foreach (['recaptcha_secret_key', 'mail_password', 'mail_fallback_password', 'storage_access_key', 'storage_secret_key'] as $field) {
            if (array_key_exists($field, $data) && blank($data[$field])) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
