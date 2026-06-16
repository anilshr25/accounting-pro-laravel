<?php

namespace App\Http\Controllers\Tenant\Expenses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Expenses\ExpensesRequest;
use App\Services\Tenant\Expenses\ExpensesService;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    protected $expenseService;

    public function __construct(ExpensesService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        return $this->expenseService->paginate($request, 25);
    }

    public function store(ExpensesRequest $request)
    {
        $expense = $this->expenseService->store($request->validated());
        if ($expense)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $expense = $this->expenseService->find($id, true);
        return response(['data' => $expense], 200);
    }

    public function update(ExpensesRequest $request, $id)
    {
        $expense = $this->expenseService->update($id, $request->validated());
        if ($expense)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->expenseService->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
