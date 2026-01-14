<?php

namespace App\Services\Tenant\Balance;

use App\Models\Tenant\Balance\Balance;
use App\Http\Resources\Tenant\Balance\BalanceResource;

class BalanceService
{
    protected $balance;
    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }
    public function paginate($request, $limit = 25)
    {
        $balance = $this->balance
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->filled('shift'), function ($query) use ($request) {
                $query->where('shift', $request->shift);
            })
            ->orderBy('date', 'ASC')
            ->paginate($request->limit ?? $limit);
        return BalanceResource::collection($balance);
    }

    public function store($data)
    {
        try {
            return $this->balance->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $balance = $this->balance->find($id);
        if (!$balance) {
            return null;
        }
        return $resource ? new BalanceResource($balance) : $balance;
    }

    public function update($id, $data)
    {
        try {
            $balance = $this->find($id);
            if (!$balance) {
                return false;
            }
            return $balance->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $balance = $this->find($id);
            if (!$balance) {
                return false;
            }
            return $balance->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
