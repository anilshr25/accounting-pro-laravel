<?php

namespace App\Services\Tenant\VendorPayment;

use App\Models\Tenant\VendorPayment\VendorPayment;
use App\Http\Resources\Tenant\VendorPayment\VendorPaymentResource;

class VendorPaymentService
{
    protected $vendor_payment;
    public function __construct(VendorPayment $vendor_payment)
    {
        $this->vendor_payment = $vendor_payment;
    }

    public function paginate($request, $limit = 25)
    {
        $vendor_payment = $this->vendor_payment->paginate($request->limit ?? $limit);
        return VendorPaymentResource::collection($vendor_payment);
    }

    public function store($data)
    {
        try {
            return $this->vendor_payment->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $vendor_payment = $this->vendor_payment->find($id);
        if (!$vendor_payment) {
            return null;
        }
        return $resource ? new VendorPaymentResource($vendor_payment) : $vendor_payment;
    }

    public function update($id, $data)
    {
        try {
            $vendor_payment = $this->find($id);
            if (!$vendor_payment) {
                return false;
            }
            return $vendor_payment->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $vendor_payment = $this->find($id);
            if (!$vendor_payment) {
                return false;
            }
            return $vendor_payment->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
