<?php

namespace App\Http\Controllers\Tenant\Scheme\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Scheme\Payment\SchemePaymentRequest;
use App\Services\Tenant\Scheme\Payment\SchemePaymentService;

class SchemePaymentController extends Controller
{
    protected $schemepayment;

    public function __construct(SchemePaymentService $schemepayment)
    {
        $this->schemepayment = $schemepayment;
    }

    public function index(Request $request)
    {
        return $this->schemepayment->paginate($request, 25);
    }

    public function store(SchemePaymentRequest $request)
    {
        $result = $this->schemepayment->store($request->validated());

        if (!$result) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Payment failed'
            ], 500);
        }

        if (isset($result['success']) && $result['success'] === false) {
            return response()->json([
                'status' => 'ERROR',
                'message' => $result['message'] ?? 'Payment not allowed'
            ], 422);
        }

        return response()->json([
            'status' => 'OK',
            'message' => 'Payment created successfully',
            'data' => $result['data'] ?? $result
        ], 200);
    }

    public function show($id)
    {
        $schemepayment = $this->schemepayment->find($id, true);
        return response(['data' => $schemepayment], 200);
    }

    public function update(SchemePaymentRequest $request, $id)
    {
        $schemepayment = $this->schemepayment->update($id, $request->validated());
        if ($schemepayment)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->schemepayment->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
