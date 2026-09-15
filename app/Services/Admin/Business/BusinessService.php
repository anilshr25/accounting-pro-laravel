<?php

namespace App\Services\Admin\Business;

use App\Http\Resources\Admin\Business\BusinessResource;
use App\Models\Business\Business;
use App\Models\Tenant\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessService
{
    protected $business;

    public function __construct(Business $business)
    {
        $this->business = $business;
    }

    public function paginate($request, $limit = 25)
    {
        $business = $this->business
            ->with('owners')
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })
            ->when($request->filled('email'), function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest('id')
            ->paginate($request->limit ?? $limit);

        return BusinessResource::collection($business);
    }

    public function search($request, $limit = 10)
    {
        $business = $this->business
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })
            ->when($request->filled('email'), function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        return BusinessResource::collection($business);
    }

    public function store($data)
{
    return DB::transaction(function () use ($data) {

        $slug = Str::slug($data['name']);

        if (
            $this->business
                ->where('slug', $slug)
                ->exists()
        ) {
            return false;
        }

        if (! empty($data['tenant_id'])) {

            $tenant = Tenant::find($data['tenant_id']);

            if (! $tenant) {
                return false;
            }

            if (
                $this->business
                    ->where('tenant_id', $tenant->id)
                    ->exists()
            ) {
                return false;
            }

        } else {

            $tenantId = (string) Str::uuid();

            $tenant = Tenant::create([
                'id' => $tenantId,
                'tenancy_db_name' => 'tenant_' . $tenantId . '_' . $slug,
            ]);
        }

        $business = $this->business->create([
            'name' => $data['name'],
            'slug' => $slug,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'] ?? 'active',
            'tenant_id' => $tenant->id,
        ]);

        return $business->load([
            'tenant',
            'owners',
        ]);
    });
}

    public function find($id, $resource = false)
    {
        $business = $this->business
            ->with([
                'tenant',
                'owners',
            ])
            ->find($id);

        if (!$business) {
            return null;
        }

        return $resource
            ? new BusinessResource($business)
            : $business;
    }

    public function update($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {

                $business = $this->find($id);

                if (! $business) {
                    return false;
                }

                $business->update([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'status' => $data['status'] ?? $business->status,
                ]);

                return $business->load([
                    'tenant',
                    'owners',
                ]);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $business = $this->find($id);

            if (!$business) {
                return false;
            }

            return $business->delete();
        } catch (\Exception $ex) {
            report($ex);

            return false;
        }
    }
}
