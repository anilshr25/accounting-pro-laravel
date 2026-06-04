<?php

namespace App\Services\Tenant\Scheme\Payment;

use App\Models\Tenant\Scheme\Payment\SchemePayment;
use App\Http\Resources\Tenant\Scheme\Payment\SchemePaymentResource;
use Illuminate\Support\Facades\DB;

class SchemePaymentService
{
    protected $schemepayment;
    public function __construct(SchemePayment $schemepayment)
    {
        $this->schemepayment = $schemepayment;
    }
    public function paginate($request, $limit = 25)
    {
        $schemepayment = $this->schemepayment
            ->when($request->filled('scheme_id'), function ($query) use ($request) {
                $query->where('scheme_id', $request->scheme_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {

                    $q->orWhere('remarks', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('amount', $search);
                    }
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->date_upto);
            })
            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('miti', '<=', $request->miti_upto);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->where('date', $request->date);
            })
            ->when($request->filled('miti'), function ($query) use ($request) {
                $query->where('miti', $request->miti);
            })
            ->latest()
            ->paginate($request->limit ?? $limit);

        return SchemePaymentResource::collection($schemepayment);
    }


    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {

                $scheme = \App\Models\Tenant\Scheme\Scheme::withSum('payments as total_paid', 'amount')
                    ->lockForUpdate()
                    ->find($data['scheme_id']);

                if (!$scheme) {
                    return [
                        'success' => false,
                        'message' => 'Scheme not found.'
                    ];
                }

                $totalPaid = $scheme->total_paid ?? 0;
                $remaining = $scheme->issued_amount - $totalPaid;

                if ($scheme->status === 'completed') {
                    return [
                        'success' => false,
                        'message' => 'Scheme already completed. No further payments allowed.'
                    ];
                }

                if ($data['amount'] > $remaining) {
                    return [
                        'success' => false,
                        'message' => 'Payment exceeds remaining balance. Remaining: ' . $remaining
                    ];
                }

                $payment = $this->schemepayment->create($data);

                $newRemaining = $remaining - $data['amount'];

                if ($newRemaining <= 0) {
                    $scheme->update([
                        'status' => 'completed'
                    ]);
                }

                return [
                    'success' => true,
                    'data' => $payment
                ];
            });
        } catch (\Exception $ex) {
            return [
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }
    }

    public function find($id, $resource = false)
    {
        $schemepayment = $this->schemepayment->find($id);
        if (!$schemepayment) {
            return null;
        }
        return $resource ? new SchemePaymentResource($schemepayment) : $schemepayment;
    }

    public function update($id, $data)
    {
        try {
            $schemepayment = $this->find($id);
            if (!$schemepayment) {
                return false;
            }
            $data = array_filter($data, function ($value) {
                return !is_null($value);
            });

            $schemepayment->update($data);

            return $schemepayment->fresh();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $schemepayment = $this->find($id);
            if (!$schemepayment) {
                return false;
            }
            return $schemepayment->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
