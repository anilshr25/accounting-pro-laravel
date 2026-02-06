<?php

namespace App\Http\Controllers\Tenant\Payment;

use App\Services\Tenant\Customer\CustomerService;
use App\Services\Tenant\Supplier\SupplierService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Payment\PaymentRequest;
use App\Services\Tenant\Payment\PaymentService;
use App\Http\Resources\Tenant\Payment\PaymentResource;

class PaymentController extends Controller
{
    protected $payment;
    protected $supplier;
    protected $customer;

    public function __construct(PaymentService $payment, SupplierService $supplier, CustomerService $customer)
    {
        $this->payment = $payment;
        $this->supplier = $supplier;
        $this->customer = $customer;
    }

    public function index(Request $request)
    {
        return $this->payment->paginate($request, 25);
    }

    public function store(PaymentRequest $request)
    {
        $data = $request->validated();

        if (!isset($data['type']) || !isset($data['party_id'])) {
            return response(['status' => 'Supplier or Customer not found.'], 500);
        }

        if ($data['type'] == 'supplier') {
            $user = $this->supplier->find($data['party_id']);
        } elseif ($data['type'] == 'customer') {
            $user = $this->customer->find($data['party_id']);
        } else {
            return response(['status' => 'Invalid type'], 500);
        }

        if (!$user) {
            return response(['status' => 'Supplier or Customer not found.'], 500);
        }

        $payment = $this->payment->store($data, $user);

        if ($payment) {
            return response(['status' => 'OK', 'data' => new PaymentResource($payment)], 200);
        }

        return response(['status' => 'ERROR'], 500);
    }


    public function show($id)
    {
        $payment = $this->payment->find($id, true);
        return response(['data' => $payment], 200);
    }

    public function update(PaymentRequest $request, $id)
    {
        $payment = $this->payment->update($id, $request->validated());
        if ($payment)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->payment->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
