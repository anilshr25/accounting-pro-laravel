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
        $fiscalYear = $request->input('fiscal_year');

        [$startYear] = explode('/', $fiscalYear);

        $mitiFrom = $startYear . '-04-01';
        $mitiUpto = ($startYear + 1) . '-03-31';
        $expenses = $this->expense
            ->whereBetween('expense_miti', [$mitiFrom, $mitiUpto])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('expense_type', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
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

            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('expense_date', '>=', $request->date_from);
            })

            ->when($request->filled('date_upto'), function ($query) use ($request) {
                $query->whereDate('expense_date', '<=', $request->date_upto);
            })

            ->when($request->filled('miti_from'), function ($query) use ($request) {
                $query->where('expense_miti', '>=', $request->miti_from);
            })
            ->when($request->filled('miti_upto'), function ($query) use ($request) {
                $query->where('expense_miti', '<=', $request->miti_upto);
            })

            ->orderBy('id', 'asc')
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
