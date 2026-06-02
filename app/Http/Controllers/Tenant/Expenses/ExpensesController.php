<?php

namespace App\Http\Controllers\Tenant\Expenses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Expenses\ExpensesRequest;
use App\Http\Resources\Tenant\Expenses\ExpensesResource;
use App\Services\Tenant\Expenses\ExpensesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    protected $expenseService;

    public function __construct(ExpensesService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index(Request $request): JsonResponse
    {
        $expenses = $this->expenseService->paginate($request);

        return response()->json([
            'success' => true,
            'message' => 'Expenses fetched successfully.',
            'data' => $expenses,
        ]);
    }

    public function store(ExpensesRequest $request): JsonResponse
    {
        $expense = $this->expenseService->store($request->validated());

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense created successfully.',
            'data' => new ExpensesResource($expense),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $expense = $this->expenseService->find($id, true);

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense fetched successfully.',
            'data' => $expense,
        ]);
    }

    public function update(ExpensesRequest $request, $id): JsonResponse
    {
        $expense = $this->expenseService->update($id, $request->validated());

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense updated successfully.',
            'data' => new ExpensesResource($expense),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $deleted = $this->expenseService->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Expense not found or could not be deleted.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully.',
        ]);
    }
}
