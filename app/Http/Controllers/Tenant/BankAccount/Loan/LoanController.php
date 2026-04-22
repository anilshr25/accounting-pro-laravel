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

        if (is_array($loan) && isset($loan['error'])) {
            return response([
                'status' => 'ERROR',
                'message' => $loan['message']
            ], 400);
        }

        if (!$loan) {
            return response([
                'status' => 'ERROR',
                'message' => 'Failed to create loan'
            ], 500);
        }

        return response([
            'status' => 'OK',
            'message' => 'Loan created successfully',
            'data' => $loan
        ], 201);
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

    public function payLoan(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $loan = $this->loan->payLoan($id, $request->amount);

        if (!$loan) {
            return response([
                'status' => 'ERROR',
                'message' => 'Loan not found'
            ], 404);
        }

        if (is_array($loan) && isset($loan['error']) && $loan['error'] === true) {
            return response([
                'status' => 'ERROR',
                'message' => $loan['message']
            ], 400);
        }

        return response([
            'status' => 'OK',
            'message' => 'Payment successful',
            'data' => $loan
        ], 200);
    }
}
