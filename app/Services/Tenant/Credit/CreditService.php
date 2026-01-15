<?php

namespace App\Services\Tenant\Credit;

use App\Models\Tenant\Credit\Credit;
use App\Http\Resources\Tenant\Credit\CreditResource;
use App\Services\Tenant\Ledger\LedgerService;

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
            ->when($request->filled('info'), function ($query) use ($request) {
                $info = $request->info;
                $query->whereHas('customer', function ($q) use ($info) {
                    $q->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%");
                });
            })
            ->orderBy('date', 'ASC')
            ->paginate($request->limit ?? $limit);
        return CreditResource::collection($credit);
    }

    public function store($data)
    {
        try {
            $credit = $this->credit->create($data);
            if ($credit) {
                LedgerService::postCredit($credit);
            }
            return $credit;
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
            $updated = $credit->update($data);
            if ($updated) {
                LedgerService::postCredit($credit);
            }
            return $updated;
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
