<?php

namespace App\Services\Tenant\Cheque;

use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Cheque\Cheque;
use App\Services\Tenant\Ledger\LedgerService;
use App\Http\Resources\Tenant\Cheque\ChequeResource;

class ChequeService
{
    protected $cheque;
    public function __construct(Cheque $cheque)
    {
        $this->cheque = $cheque;
    }

    public function paginate($request, $limit = 25)
    {
        $cheque = $this->cheque->paginate($request->limit ?? $limit);
        return ChequeResource::collection($cheque);
    }

    public function store($data, $user)
    {
        try {
            return DB::transaction(function () use ($data, $user) {
                $cheque = $this->cheque->create($data);
                $cheque->party()->associate($user);
                LedgerService::postCheaque($cheque);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $cheque = $this->cheque->find($id);
        if (!$cheque) {
            return null;
        }
        return $resource ? new ChequeResource($cheque) : $cheque;
    }

    public function update($id, $data)
    {
        try {
            $cheque = $this->find($id);
            if (!$cheque) {
                return false;
            }
            return $cheque->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $cheque = $this->find($id);
            if (!$cheque) {
                return false;
            }
            return $cheque->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
