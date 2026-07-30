<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tenant\Payroll\PayrollService;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        $month = $request->month;

        $payroll = $this->payrollService->getPayroll($month);

        return response()->json([
            'success' => true,
            'message' => 'Payroll fetched successfully.',
            'data' => $payroll,
        ]);
    }
}
