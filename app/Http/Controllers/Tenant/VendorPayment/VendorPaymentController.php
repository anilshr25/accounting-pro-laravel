<?php

namespace App\Http\Controllers\Tenant\VendorPayment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\VendorPayment\VendorPaymentRequest;
use App\Services\Tenant\VendorPayment\VendorPaymentService;

class VendorPaymentController extends Controller
{
    protected $vendor_payment;

    public function __construct(VendorPaymentService $vendor_payment)
    {
        $this->vendor_payment = $vendor_payment;
    }

    public function index(Request $request)
    {
        return $this->vendor_payment->paginate($request, 25);
    }

    public function store(VendorPaymentRequest $request)
    {
        $vendor_payment = $this->vendor_payment->store($request->validated());
        if ($vendor_payment)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $vendor_payment = $this->vendor_payment->find($id);
        return response(['data' => $vendor_payment], 200);
    }

    public function update(VendorPaymentRequest $request, $id)
    {
        $vendor_payment = $this->vendor_payment->update($id, $request->validated());
        if ($vendor_payment)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->vendor_payment->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
