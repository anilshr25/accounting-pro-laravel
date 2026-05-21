<?php

namespace App\Http\Controllers\Tenant\Employee;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Employee\EmployeeRequest;
use App\Services\Tenant\Employee\EmployeeService;

class EmployeeController extends Controller
{
    protected $employee;

    public function __construct(EmployeeService $employee)
    {
        $this->employee = $employee;
    }

    public function index(Request $request)
    {
        return $this->employee->paginate($request, 25);
    }

    public function search(Request $request)
    {
        return $this->employee->search($request, 10);
    }

    public function store(EmployeeRequest $request)
    {
        $employee = $this->employee->store($request->validated());
        if ($employee)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $employee = $this->employee->find($id, true);
        return response(['data' => $employee], 200);
    }

    public function update(EmployeeRequest $request, $id)
    {
        $employee = $this->employee->update($id, $request->validated());
        if ($employee)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->employee->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
