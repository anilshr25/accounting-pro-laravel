<?php

namespace App\Services\Tenant\Payment;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Payment\Payment;
use App\Services\Tenant\Ledger\LedgerService;
use App\Http\Resources\Tenant\Payment\PaymentResource;

class PaymentService
{
    protected $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
    public function paginate($request, $limit = 25)
    {
        $fiscalYear = $request->input('fiscal_year');

        [$startYear] = explode('/', $fiscalYear);

        $mitiFrom = $startYear . '-04-01';
        $mitiUpto = ($startYear + 1) . '-03-31';

        $payment = $this->payment
            ->whereBetween('miti', [$mitiFrom, $mitiUpto])
            ->when($request->filled('party_type'), function ($query) use ($request) {
                $query->where('party_type', $request->party_type);
            })
            ->when($request->filled('party_id'), function ($query) use ($request) {
                $query->where('party_id', $request->party_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->whereHasMorph('party', ['supplier', 'customer'], function ($morph, $type) use ($search) {

                        $morph->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");

                        if ($type === 'supplier') {
                            $morph->orWhere('pan', 'like', "%{$search}%");
                        }
                    })

                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere('shift', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('amount', $search);
                    }
                });
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
            ->when($request->filled('remarks'), function ($query) use ($request) {
                $query->where('remarks', 'like', "%{$request->remarks}%");
            })
            ->when(
                $request->filled('date_from'),
                fn($q) =>
                $q->whereDate('date', '>=', $request->date_from)
            )
            ->when(
                $request->filled('date_upto'),
                fn($q) =>
                $q->whereDate('date', '<=', $request->date_upto)
            )
            ->when(
                $request->filled('miti_from'),
                fn($q) =>
                $q->where('miti', '>=', $request->miti_from)
            )
            ->when(
                $request->filled('miti_upto'),
                fn($q) =>
                $q->where('miti', '<=', $request->miti_upto)
            )
            ->orderBy('date', 'DESC')
            ->paginate($request->limit ?? $limit);
        return PaymentResource::collection($payment);
    }

    public function store($data, $user)
    {
        if (!$user) {
            return false;
        }

        try {
            return DB::transaction(function () use ($data, $user) {

                $payment = new Payment(collect($data)->only((new Payment())->getFillable())->toArray());

                $payment->party()->associate($user);

                $payment->save();

                try {
                    LedgerService::postPayment($payment);
                } catch (\Exception $ex) {
                    logger()->error('LedgerService postPayment failed: ' . $ex->getMessage());
                }

                return $payment;
            });
        } catch (\Exception $th) {
            logger()->error('Payment store failed: ' . $th->getMessage());
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
        $payment = $this->find($id);
        if (!$payment) {
            return false;
        }
        $fillableData = collect($data)->only($payment->getFillable())->toArray();

        $updated = $payment->update($fillableData);
        try {
            if ($updated) {
                LedgerService::postPayment($payment);
            }
        } catch (\Exception $ex) {
            logger()->error('LedgerService postPayment error: ' . $ex->getMessage());
        }
        return $updated;
    }

    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $payment = $this->find($id);

                if (!$payment) {
                    throw new \Exception('Payment not found');
                }

                LedgerService::deleteByReference(
                    'payment',
                    $payment->id
                );

                $payment->delete();
            });

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
