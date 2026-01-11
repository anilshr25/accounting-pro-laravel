<?php

namespace App\Http\Controllers\Tenant\BankAccount;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BankAccount\BankAccountRequest;
use App\Services\Tenant\BankAccount\BankAccountService;

class BankAccountController extends Controller
{
    protected $bank_account;

    public function __construct(BankAccountService $bank_account)
    {
        $this->bank_account = $bank_account;
    }

    public function index(Request $request)
    {
        return $this->bank_account->paginate($request, 25);
    }

    public function store(BankAccountRequest $request)
    {
        $bank_account = $this->bank_account->store($request->validated());
        if ($bank_account)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $bank_account = $this->bank_account->find($id, true);
        return response(['data' => $bank_account], 200);
    }

    public function update(BankAccountRequest $request, $id)
    {
        $bank_account = $this->bank_account->update($id, $request->validated());
        if ($bank_account)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->bank_account->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
