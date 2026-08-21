<?php

namespace App\Services\Tenant\Kye\Service;

use App\Models\Tenant\Kye\Service\KyeService;
use App\Http\Resources\Tenant\Kye\Service\KyeServiceResource;

class KyeServicesService
{
    protected $service;

    public function __construct(KyeService $service)
    {
        $this->service = $service;
    }

    public function paginate($request, $limit = 25)
    {
        $services = $this->service
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('relationship', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('employee_id'), function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->orderBy('id', 'asc')
            ->paginate($request->limit ?? $limit);

        return KyeServiceResource::collection($services);
    }

    public function store($data)
    {
        try {
            return $this->service->create($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $service = $this->service->find($id);

        if (!$service) {
            return null;
        }

        return $resource
            ? new KyeServiceResource($service)
            : $service;
    }

    public function update($id, $data)
    {
        try {
            $service = $this->find($id);

            if (!$service) {
                return false;
            }

            $service->update($data);

            return $service;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $service = $this->find($id);

            if (!$service) {
                return false;
            }

            return $service->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
