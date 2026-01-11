<?php

namespace App\Services\Tenant\Customer;

use App\Models\Tenant\Customer\Customer;
use App\Http\Resources\Tenant\Customer\CustomerResource;

class CustomerService
{
    protected $customer;
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function paginate($request, $limit = 25)
    {
        $customer = $this->customer->paginate($request->limit ?? $limit);
        return CustomerResource::collection($customer);
    }

    public function store($data)
    {
        try {
            return $this->customer->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $customer = $this->customer->find($id);
        if($customer){
            $resource ? new CustomerResource($resource) : $resource;
        }
        return null;
    }

    public function update($id, $data)
    {
        try {
            $customer = $this->find($id);
            if (!$customer) {
                return false;
            }
            return $customer->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $customer = $this->find($id);
            if (!$customer) {
                return false;
            }
            return $customer->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
