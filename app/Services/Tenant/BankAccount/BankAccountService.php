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
        $bank_account = $this->bank_account
            ->when($request->filled('bank_name'), function ($query) use ($request) {
                $query->where('bank_name', $request->bank_name);
            })
            ->when($request->filled('account_number'), function ($query) use ($request) {
                $query->where('account_number', 'like', "%{$request->account_number}%");
            })
            ->when($request->filled('account_type'), function ($query) use ($request) {
                $query->where('account_type', $request->account_type);
            })
            ->paginate($request->limit ?? $limit);
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

    public function find($id, $resource = false)
    {
        $bank_account = $this->bank_account->find($id);
        if (!$bank_account) {
            return null;
        }
        return $resource ? new BankAccountResource($bank_account) : $bank_account;
    }

    public function show($id)
    {
        $bankAccount = $this->find($id);
        if ($bankAccount)
            return new BankAccountResource($bankAccount);
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
