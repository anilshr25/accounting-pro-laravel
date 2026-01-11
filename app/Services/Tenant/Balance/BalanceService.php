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
        $balance = $this->balance->paginate($request->limit ?? $limit);
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
        if($balance){
            $resource ? new BalanceResource($resource) : $resource;
        }
        return null;
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
