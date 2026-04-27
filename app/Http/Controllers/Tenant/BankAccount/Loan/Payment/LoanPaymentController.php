<?php

namespace App\Http\Controllers\Tenant\BankAccount\Loan\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Tenant\BankAccount\Loan\Payment\LoanPaymentRequest;
use App\Services\Tenant\BankAccount\Loan\Payment\LoanPaymentService;
use App\Http\Resources\Tenant\BankAccount\Loan\Payment\LoanPaymentResource;

class LoanPaymentController extends Controller
{
    protected $service;

    public function __construct(LoanPaymentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return LoanPaymentResource::collection(
            $this->service->paginate($request)
        );
    }

    public function store(LoanPaymentRequest $request)
    {
        $payment = $this->service->store($request->validated());

        if (is_array($payment) && isset($payment['error'])) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $payment['message']
            ], 400);
        }

        return response()->json([
            'status' => 'OK',
            'message' => 'Payment created successfully',
        ], 201);
    }

    public function show($id)
    {
        $payment = $this->service->find($id);

        if (!$payment) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Payment not found'
            ], 404);
        }

        return new LoanPaymentResource($payment);
    }

    public function update(LoanPaymentRequest $request, $id)
    {
        $payment = $this->service->update($id, $request->validated());

        if (!$payment) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'message' => 'Payment updated successfully'
        ]);
    }

    public function destroy($id)
    {
        if (!$this->service->delete($id)) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'message' => 'Payment deleted successfully'
        ]);
    }
}
