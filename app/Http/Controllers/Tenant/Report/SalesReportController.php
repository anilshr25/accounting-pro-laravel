<?php

namespace App\Http\Controllers\Tenant\Report;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Report\SalesReportService;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    protected SalesReportService $salesReportService;

    public function __construct(SalesReportService $salesReportService)
    {
        $this->salesReportService = $salesReportService;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->salesReportService->getReport($request)
        );
    }
}
