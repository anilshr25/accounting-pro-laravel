<?php

namespace App\Services\Tenant\BankAccount;

use App\Models\Tenant\BankAccount\BankAccount;
use App\Http\Resources\Tenant\BankAccount\BankAccountResource;

class BankAccountService
{
    protected $bank_account;

    public function __construct(BankAccount $bank_account)
    {
        $this->bank_account = $bank_account;
    }

    public function paginate($request, $limit = 25)
    {
        $bank_account = $this->bank_account->paginate($request->limit ?? $limit);
        return BankAccountResource::collection($bank_account);
    }

    public function store($data)
    {
        try {
            return $this->bank_account->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id)
    {
        return $this->bank_account->find($id);
    }

    public function show($id)
    {
        $bankAccount = $this->find($id);
        if($bankAccount)
            return new BankAccountResource($bankAccount);
        return null;
    }

    public function update($id, $data)
    {
        try {
            $bank_account = $this->find($id);
            if (!$bank_account) {
                return false;
            }
            return $bank_account->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $bank_account = $this->find($id);
            if (!$bank_account) {
                return false;
            }
            return $bank_account->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
