<?php

namespace App\Http\Controllers\Tenant\Report;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Report\LoanReportService;
use Illuminate\Http\Request;

class LoanReportController extends Controller
{
    protected $loanReportService;

    public function __construct(LoanReportService $loanReportService)
    {
        $this->loanReportService = $loanReportService;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->loanReportService->getReport($request),
            200
        );
    }
}
