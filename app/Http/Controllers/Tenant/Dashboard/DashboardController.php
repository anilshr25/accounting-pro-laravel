<?php

namespace App\Http\Controllers\Tenant\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Dashboard\DashboardService;
use App\Http\Requests\Tenant\Dashboard\DashboardRequest;
use App\Http\Resources\Tenant\Dashboard\DashboardResource;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(DashboardRequest $request)
    {
        $data = $this->dashboardService->getSummary($request->input('date'));
        return new DashboardResource($data);
    }
}
