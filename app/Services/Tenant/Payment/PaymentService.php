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
        $payment = $this->payment
            ->when($request->filled('party_type'), function ($query) use ($request) {
                $query->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($query) use ($request) {
                $query->where('party_id', $request->party_id);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('miti'), function ($query) use ($request) {
                $query->where('miti', 'like', "%{$request->miti}%");
            })
            ->when($request->filled('amount'), function ($query) use ($request) {
                $query->where('amount', $request->amount);
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->when($request->filled('shift'), function ($query) use ($request) {
                $query->where('shift', $request->shift);
            })
            ->when($request->filled('transaction_id'), function ($query) use ($request) {
                $query->where('transaction_id', $request->transaction_id);
            })
            ->when($request->filled('is_posted'), function ($query) use ($request) {
                $query->where('is_posted', 'like', "%{$request->is_posted}%");
            })
            ->when($request->filled('remarks'), function ($query) use ($request) {
                $query->where('remarks', 'like', "%{$request->remarks}%");
            })
            ->paginate($request->limit ?? $limit);
        return PaymentResource::collection($payment);
    }

    public function store($data, $user)
    {
            return DB::transaction(function () use ($data, $user) {

                $payment = new Payment($data);

                $payment->party()->associate($user);

                $payment->save();

                LedgerService::postPayment($payment);

                return $payment;
            });

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
