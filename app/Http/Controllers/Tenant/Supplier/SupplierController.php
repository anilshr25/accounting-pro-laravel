<?php

namespace App\Http\Controllers\Tenant\Supplier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Supplier\SupplierRequest;
use App\Services\Tenant\Supplier\SupplierService;

class SupplierController extends Controller
{
    protected $supplier;

    public function __construct(SupplierService $supplier)
    {
        $this->supplier = $supplier;
    }

    public function index(Request $request)
    {
        return $this->supplier->paginate($request, 25);
    }

    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplier->store($request->validated());
        if ($supplier)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function show($id)
    {
        $supplier = $this->supplier->find($id, true);
        return response(['data' => $supplier], 200);
    }

    public function update(SupplierRequest $request, $id)
    {
        $supplier = $this->supplier->update($id, $request->validated());
        if ($supplier)
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }

    public function destroy($id)
    {
        if ($this->supplier->delete($id))
            return response(['status' => 'OK'], 200);
        return response(['status' => 'ERROR'], 500);
    }
}
