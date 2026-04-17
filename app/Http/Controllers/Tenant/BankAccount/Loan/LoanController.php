<?php

namespace App\Http\Controllers\Tenant\BankAccount\Loan;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BankAccount\Loan\LoanRequest;
use App\Services\Tenant\BankAccount\Loan\LoanService;

class LoanController extends Controller
{
    protected $loan;

    public function __construct(LoanService $loan)
    {
        $this->loan = $loan;
    }

    public function index(Request $request)
    {
        return $this->loan->paginate($request, 25);
    }

    public function store(LoanRequest $request)
    {
        $loan = $this->loan->store($request->validated());
        if ($loan)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $loan = $this->loan->find($id, true);
        return response(['data' => $loan], 200);
    }

    public function update(LoanRequest $request, $id)
    {
        $loan = $this->loan->update($id, $request->validated());
        if ($loan)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->loan->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
