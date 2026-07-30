<?php

namespace App\Http\Controllers\Tenant\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Tenant\Report\ChequeReportService;

class ChequeReportController extends Controller
{
    protected $chequeReportService;

    public function __construct(ChequeReportService $chequeReportService)
    {
        $this->chequeReportService = $chequeReportService;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->chequeReportService->getReport($request)
        );
    }
}
