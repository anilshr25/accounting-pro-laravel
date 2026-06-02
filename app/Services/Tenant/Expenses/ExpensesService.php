<?php

namespace App\Services\Tenant\Expenses;

use App\Models\Tenant\Expenses\Expenses;
use App\Http\Resources\Tenant\Expenses\ExpensesResource;

class ExpensesService
{
    protected $expense;

    public function __construct(Expenses $expense)
    {
        $this->expense = $expense;
    }

    public function paginate($request, $limit = 25)
    {
        $expenses = $this->expense
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('expense_type'), function ($query) use ($request) {
                $query->where('expense_type', $request->expense_type);
            })

            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })

            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })

            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('expense_date', '>=', $request->from_date);
            })

            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('expense_date', '<=', $request->to_date);
            })

            ->latest()
            ->paginate($request->limit ?? $limit);

        return ExpensesResource::collection($expenses);
    }

    public function store($data)
    {
        try {
            return $this->expense->create($data);
        } catch (\Exception $ex) {
            dd($ex->getMessage());
        }
    }

    public function find($id, $resource = false)
    {
        $expense = $this->expense->find($id);

        if (!$expense) {
            return null;
        }

        return $resource
            ? new ExpensesResource($expense)
            : $expense;
    }

    public function update($id, $data)
    {
        try {
            $expense = $this->find($id);

            if (!$expense) {
                return false;
            }

            $expense->update($data);

            return $expense;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $expense = $this->find($id);

            if (!$expense) {
                return false;
            }

            return $expense->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
