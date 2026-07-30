<?php

namespace App\Http\Controllers\Tenant\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Tenant\Report\PaymentReportService;

class PaymentReportController extends Controller
{
    protected $paymentReportService;

    public function __construct(PaymentReportService $paymentReportService)
    {
        $this->paymentReportService = $paymentReportService;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->paymentReportService->getReport($request)
        );
    }
}
