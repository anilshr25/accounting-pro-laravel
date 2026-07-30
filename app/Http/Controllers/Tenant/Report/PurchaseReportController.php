<?php

namespace App\Http\Controllers\Tenant\Report;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Report\PurchaseReportService;
use Illuminate\Http\Request;

class PurchaseReportController extends Controller
{
    protected PurchaseReportService $purchaseReportService;

    public function __construct(PurchaseReportService $purchaseReportService)
    {
        $this->purchaseReportService = $purchaseReportService;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->purchaseReportService->getReport($request)
        );
    }
}
