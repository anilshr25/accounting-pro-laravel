<?php

namespace App\Services\Tenant\BankAccount;

use App\Models\Tenant\BankAccount\BankAccount;
use App\Http\Resources\Tenant\BankAccount\BankAccountResource;
use Illuminate\Support\Facades\DB;

class BankAccountService
{
    protected $bank_account;
    public function __construct(BankAccount $bank_account)
    {
        $this->bank_account = $bank_account;
    }
    private function balanceWithCheque()
    {
        return DB::raw('
            balance + (
                SELECT COALESCE(SUM(c.amount), 0)
                FROM cheques c
                WHERE c.bank_account_id = bank_accounts.id
                  AND c.type = "supplier"
                  AND c.status = "pending"
            ) as balance
        ');
    }

    public function paginate($request, $limit = 25)
    {
        $bank_account = $this->bank_account
            ->select(
                'id',
                'bank_name',
                'account_number',
                'account_type',
                $this->balanceWithCheque()
            )
            ->when(
                $request->filled('bank_name'),
                fn($q) =>
                $q->where('bank_name', $request->bank_name)
            )
            ->when(
                $request->filled('account_number'),
                fn($q) =>
                $q->where('account_number', 'like', "%{$request->account_number}%")
            )
            ->when(
                $request->filled('account_type'),
                fn($q) =>
                $q->where('account_type', $request->account_type)
            )
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
        $bank_account = $this->bank_account
            ->where('id', $id)
            ->select(
                'id',
                'bank_name',
                'account_number',
                'account_type',
                $this->balanceWithCheque()
            )
            ->first();
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
