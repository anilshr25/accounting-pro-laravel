<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\SiteSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SiteSetting\SiteSettingRequest;
use App\Http\Resources\Tenant\SiteSetting\SiteSettingResource;
use App\Services\Tenant\SiteSetting\SiteSettingService;
use Illuminate\Http\JsonResponse;

final class SiteSettingController extends Controller
{
    public function __construct(private readonly SiteSettingService $settings) {}

    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->settings->get()]);
    }

    public function update(SiteSettingRequest $request): JsonResponse
    {
        $setting = $this->settings->storeOrUpdate($request->validated());

        return response()->json([
            'status' => 'OK',
            'data' => new SiteSettingResource($setting->refresh()),
        ]);
    }
}
