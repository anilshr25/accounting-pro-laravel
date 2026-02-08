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
            'type'  => $request->input('type'),
            'date'  => $request->input('date'),
            'month' => $request->input('month'),
            'year'  => $request->input('year'),
        ];

        $data = $this->dashboardService->getSummary($filters);

        return new DashboardResource($data);
    }
}
