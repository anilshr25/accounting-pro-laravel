<?php

namespace App\Services\Tenant\Employee;

use App\Models\Tenant\Employee\Employee;
use App\Http\Resources\Tenant\Employee\EmployeeResource;

class EmployeeService
{
    protected $employee;
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }
    public function paginate($request, $limit = 25)
    {
        $employee = $this->employee
            ->when($request->filled('info'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $info = $request->info;
                    $sub->where('first_name', 'like', "%{$info}%")
                        ->orWhere('last_name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%");
                });
            })
            ->when($request->filled('address'), function ($query) use ($request) {
                $query->where('address', 'like', "%{$request->address}%");
            })
            ->when($request->filled('employment_type'), function ($query) use ($request) {
                $query->where('employment_type', $request->employment_type);
            })
            ->when($request->filled('designation'), function ($query) use ($request) {
                $query->where('designation', 'like', "%{$request->designation}%");
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->paginate($request->limit ?? $limit);
        return EmployeeResource::collection($employee);
    }

    public function search($request, $limit = 10)
    {
        $employee = $this->employee
            ->when($request->filled('info'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $info = $request->info;
                    $sub->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%");
                });
            })
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
        return EmployeeResource::collection($employee);
    }

    public function store($data)
    {
        try {
            return $this->employee->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $employee = $this->employee->find($id);
        if (!$employee) {
            return null;
        }
        return $resource ? new EmployeeResource($employee) : $employee;
    }

    public function update($id, $data)
    {
        try {
            $employee = $this->find($id);
            if (!$employee) {
                return false;
            }
            return $employee->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $employee = $this->find($id);
            if (!$employee) {
                return false;
            }
            return $employee->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
