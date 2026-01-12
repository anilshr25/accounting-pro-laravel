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
        $credit = $this->credit->paginate($request->limit ?? $limit);
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
