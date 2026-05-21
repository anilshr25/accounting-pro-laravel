<?php

namespace App\Services\Tenant\Salary;

use App\Models\Tenant\Salary\Salary;
use App\Http\Resources\Tenant\Salary\SalaryResource;

class SalaryService
{
    protected $salary;
    public function __construct(Salary $salary)
    {
        $this->salary = $salary;
    }
    public function paginate($request, $limit = 25)
    {
        $salary = $this->salary
            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->where('month', $request->month);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->latest()
            ->paginate($request->limit ?? $limit);

        return SalaryResource::collection($salary);
    }

    public function search($request, $limit = 10)
    {
        $salary = $this->salary
            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->where('month', 'like', "%{$request->month}%");
            })
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
        return SalaryResource::collection($salary);
    }

    public function store($data)
    {
        try {
            return $this->salary->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $salary = $this->salary->find($id);
        if (!$salary) {
            return null;
        }
        return $resource ? new SalaryResource($salary) : $salary;
    }

    public function update($id, $data)
    {
        try {
            $salary = $this->find($id);
            if (!$salary) {
                return false;
            }
            return $salary->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $salary = $this->find($id);
            if (!$salary) {
                return false;
            }
            return $salary->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
