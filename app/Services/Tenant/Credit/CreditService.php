<?php

namespace App\Services\Tenant\Credit;

use App\Models\Tenant\Credit\Credit;
use App\Http\Resources\Tenant\Credit\CreditResource;

class CreditService
{
    protected $credit;
    public function __construct(Credit $credit)
    {
        $this->credit = $credit;
    }
    public function paginate($request, $limit = 25)
    {
        $credit = $this->credit
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('amount'), function ($query) use ($request) {
                $query->where('amount', $request->amount);
            })
            ->when($request->filled('return_amount'), function ($query) use ($request) {
                $query->where('return_amount', $request->return_amount);
            })
            ->when($request->filled('description'), function ($query) use ($request) {
                $query->where('description', 'like', "%{$request->description}%");
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('miti'), function ($query) use ($request) {
                $query->where('miti', 'like', "%{$request->miti}%");
            })
            ->when($request->filled('shift'), function ($query) use ($request) {
                $query->where('shift', $request->shift);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->orderBy('date', 'ASC')
            ->paginate($request->limit ?? $limit);
        return CreditResource::collection($credit);
    }

    public function store($data)
    {
        try {
            return $this->credit->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $credit = $this->credit->find($id);
        if (!$credit) {
            return null;
        }
        return $resource ? new CreditResource($credit) : $credit;
    }

    public function update($id, $data)
    {
        try {
            $credit = $this->find($id);
            if (!$credit) {
                return false;
            }
            return $credit->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $credit = $this->find($id);
            if (!$credit) {
                return false;
            }
            return $credit->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
