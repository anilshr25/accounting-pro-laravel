<?php

namespace App\Http\Controllers\Tenant\Report;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Report\CreditReportService;
use Illuminate\Http\Request;

class CreditReportController extends Controller
{
    protected $creditReportService;

    public function __construct(CreditReportService $creditReportService)
    {
        $this->creditReportService = $creditReportService;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->creditReportService->getReport($request)
        );
    }
}
