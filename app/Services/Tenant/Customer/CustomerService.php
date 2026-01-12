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
        $customer = $this->customer
            ->when($request->filled('info'), function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $info = $request->info;
                    $sub->where('name', 'like', "%{$info}%")
                        ->orWhere('email', 'like', "%{$info}%")
                        ->orWhere('phone', 'like', "%{$info}%");
                });
            })
            ->when($request->filled('address'), function ($query) use ($request) {
                $query->where('address', 'like', "%{$request->address}%");
            })
            ->when($request->filled('credit_balance'), function ($query) use ($request) {
                $query->where('credit_balance', $request->credit_balance);
            })
            ->when($request->filled('vat'), function ($query) use ($request) {
                $query->where('vat', 'like', "%{$request->vat}%");
            })
            ->paginate($request->limit ?? $limit);
        return CustomerResource::collection($customer);
    }

    public function search($request, $limit = 10)
    {
        $customer = $this->customer
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
        if (!$customer) {
            return null;
        }
        return $resource ? new CustomerResource($customer) : $customer;
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
