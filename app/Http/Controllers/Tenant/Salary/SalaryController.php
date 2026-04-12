<?php

namespace App\Http\Controllers\Tenant\Salary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Salary\SalaryRequest;
use App\Services\Tenant\Salary\SalaryService;

class SalaryController extends Controller
{
    protected $salary;

    public function __construct(SalaryService $salary)
    {
        $this->salary = $salary;
    }

    public function index(Request $request)
    {
        return $this->salary->paginate($request, 25);
    }

    public function search(Request $request)
    {
        return $this->salary->search($request, 10);
    }

    public function store(SalaryRequest $request)
    {
        $salary = $this->salary->store($request->validated());
        if ($salary)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $salary = $this->salary->find($id, true);
        return response(['data' => $salary], 200);
    }

    public function update(SalaryRequest $request, $id)
    {
        $salary = $this->salary->update($id, $request->validated());
        if ($salary)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->salary->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
