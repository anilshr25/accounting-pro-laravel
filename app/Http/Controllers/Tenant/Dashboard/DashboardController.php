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
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'period' => $request->input('period'),
        ];

        $data = $this->dashboardService->getSummary($filters);

        return new DashboardResource($data);
    }
}
