<?php

namespace App\Services\Tenant\Procurement;

use App\Models\Tenant\Procurement\Procurement;
use App\Http\Resources\Tenant\Procurement\ProcurementResource;
use Illuminate\Support\Facades\DB;

class ProcurementService
{
    protected $procurement;

    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    public function paginate($request, $limit = 25)
    {
        $procurements = $this->procurement
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->name}%");
            })
            ->when($request->filled('unit'), function ($query) use ($request) {
                $query->where('unit', $request->unit);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'ASC')
            ->paginate($request->limit ?? $limit);

        return ProcurementResource::collection($procurements);
    }

    public function store(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return $this->procurement->create($data);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $procurement = $this->procurement->find($id);

        if (!$procurement) {
            return null;
        }

        return $resource ? new ProcurementResource($procurement) : $procurement;
    }

    public function update($id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $procurement = $this->find($id);

                if (!$procurement) {
                    return false;
                }

                return $procurement->update($data);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $procurement = $this->find($id);

            if (!$procurement) {
                return false;
            }

            return $procurement->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
