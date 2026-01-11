<?php

namespace App\Services\Tenant\Payment;

use App\Models\Tenant\Payment\Payment;
use App\Http\Resources\Tenant\Payment\PaymentResource;
use App\Services\Tenant\Ledger\LedgerService;
use DB;

class PaymentService
{
    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function paginate($request, $limit = 25)
    {
        $payment = $this->payment->paginate($request->limit ?? $limit);
        return PaymentResource::collection($payment);
    }

    public function store($data, $user)
    {
        try {
            return DB::transaction(function () use ($data, $user) {
                $payment = $this->payment->create($data);
                $payment->party()->associate($user);
                LedgerService::postPayment($payment);
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $payment = $this->payment->find($id);
        if (!$payment) {
            return null;
        }
        return $resource ? new PaymentResource($payment) : $payment;
    }

    public function update($id, $data)
    {
        try {
            $payment = $this->find($id);
            if (!$payment) {
                return false;
            }
            return $payment->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $payment = $this->find($id);
            if (!$payment) {
                return false;
            }
            return $payment->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
