<?php

namespace App\Services\Tenant\Cheque;

use App\Models\Tenant\Cheque\Cheque;
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

    public function store($data)
    {
        try {
            return $this->cheque->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id)
    {
        return $this->cheque->find($id);
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
