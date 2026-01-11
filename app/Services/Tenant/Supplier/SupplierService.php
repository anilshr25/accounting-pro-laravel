<?php

namespace App\Services\Tenant\Supplier;

use App\Models\Tenant\Supplier\Supplier;
use App\Http\Resources\Tenant\Supplier\SupplierResource;

class SupplierService
{
    protected $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function paginate($request, $limit = 25)
    {
        $supplier = $this->supplier->paginate($request->limit ?? $limit);
        return SupplierResource::collection($supplier);
    }

    public function store($data)
    {
        try {
            return $this->supplier->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $supplier = $this->supplier->find($id);
        if($supplier){
            $resource ? new SupplierResource($resource) : $resource;
        }
        return null;
    }

    public function update($id, $data)
    {
        try {
            $supplier = $this->find($id);
            if (!$supplier) {
                return false;
            }
            return $supplier->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $supplier = $this->find($id);
            if (!$supplier) {
                return false;
            }
            return $supplier->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
