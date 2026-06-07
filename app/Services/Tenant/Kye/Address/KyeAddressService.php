<?php

namespace App\Services\Tenant\Kye\Address;

use App\Models\Tenant\Kye\Address\KyeAddress;
use App\Http\Resources\Tenant\Kye\Address\KyeAddressResource;

class KyeAddressService
{
    protected $address;

    public function __construct(KyeAddress $address)
    {
        $this->address = $address;
    }

    public function paginate($request, $limit = 25)
    {
        $addresses = $this->address
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('type', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('ward_number', 'like', "%{$search}%")
                        ->orWhere('plus_code', 'like', "%{$search}%")
                        ->orWhere('locality', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('kye_id'), function ($query) use ($request) {
                $query->where('kye_id', $request->kye_id);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderBy('id', 'asc')
            ->paginate($request->limit ?? $limit);

        return KyeAddressResource::collection($addresses);
    }

    public function store($data)
    {
        try {
            return $this->address->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $address = $this->address->find($id);

        if (!$address) {
            return null;
        }

        return $resource
            ? new KyeAddressResource($address)
            : $address;
    }

    public function update($id, $data)
    {
        try {
            $address = $this->find($id);

            if (!$address) {
                return false;
            }

            $address->update($data);

            return $address;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $address = $this->find($id);

            if (!$address) {
                return false;
            }

            return $address->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
