<?php

namespace App\Services\Tenant\SiteSetting;

use App\Models\Tenant\SiteSetting\SiteSetting;
use App\Http\Resources\Tenant\SiteSetting\SiteSettingResource;

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

    public function storeOrUpdate($data)
    {
        try {
            $setting = $this->site_setting->first();
            if ($setting) {
                $setting->update($data);
                return $setting;
            }
            return $this->site_setting->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }
}
