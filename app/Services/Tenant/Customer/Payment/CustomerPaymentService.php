<?php

namespace App\Services\Tenant\Customer\Payment;

use App\Models\Tenant\Customer\Payment\CustomerPayment;
use App\Http\Resources\Tenant\Customer\Payment\CustomerPaymentResource;

class CustomerPaymentService
{
    protected $customer_payment;
    public function __construct(CustomerPayment $customer_payment)
    {
        $this->customer_payment = $customer_payment;
    }

    public function paginate($request, $limit = 25)
    {
        $customer_payment = $this->customer_payment->paginate($request->limit ?? $limit);
        return CustomerPaymentResource::collection($customer_payment);
    }

    public function store($data)
    {
        try {
            return $this->customer_payment->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $customer_payment = $this->customer_payment->find($id);
        if($customer_payment){
            $resource ? new CustomerPaymentResource($resource) : $resource;
        }
        return null;
    }

    public function update($id, $data)
    {
        try {
            $customer_payment = $this->find($id);
            if (!$customer_payment) {
                return false;
            }
            return $customer_payment->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $customer_payment = $this->find($id);
            if (!$customer_payment) {
                return false;
            }
            return $customer_payment->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
