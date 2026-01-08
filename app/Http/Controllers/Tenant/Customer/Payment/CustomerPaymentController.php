<?php

namespace App\Http\Controllers\Tenant\Customer\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Customer\Payment\CustomerPaymentRequest;
use App\Services\Tenant\Customer\Payment\CustomerPaymentService;

class CustomerPaymentController extends Controller
{
    protected $customer_payment;

    public function __construct(CustomerPaymentService $customer_payment)
    {
        $this->customer_payment = $customer_payment;
    }

    public function index(Request $request)
    {
        return $this->customer_payment->paginate($request, 25);
    }

    public function store(CustomerPaymentRequest $request)
    {
        $customer_payment = $this->customer_payment->store($request->validated());
        if ($customer_payment)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function update(CustomerPaymentRequest $request, $id)
    {
        $customer_payment = $this->customer_payment->update($id, $request->validated());
        if ($customer_payment)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->customer_payment->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
