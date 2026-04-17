<?php

namespace App\Services\Tenant\Scheme\Payment;

use App\Models\Tenant\Scheme\Payment\SchemePayment;
use App\Http\Resources\Tenant\Scheme\Payment\SchemePaymentResource;

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
            return $this->schemepayment->create($data);
        } catch (\Exception $ex) {
            return false;
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
