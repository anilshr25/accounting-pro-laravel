<?php

namespace App\Http\Controllers\Tenant\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Customer\CustomerRequest;
use App\Services\Tenant\Customer\CustomerService;

class CustomerController extends Controller
{
    protected $customer;

    public function __construct(CustomerService $customer)
    {
        $this->customer = $customer;
    }

    public function index(Request $request)
    {
        return $this->customer->paginate($request, 25);
    }

    public function search(Request $request)
    {
        return $this->customer->search($request, 10);
    }

    public function store(CustomerRequest $request)
    {
        $customer = $this->customer->store($request->validated());
        if ($customer)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $customer = $this->customer->find($id, true);
        return response(['data' => $customer], 200);
    }

    public function update(CustomerRequest $request, $id)
    {
        $customer = $this->customer->update($id, $request->validated());
        if ($customer)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->customer->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
