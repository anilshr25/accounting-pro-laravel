<?php

namespace App\Services\Permission;

use App\Http\Resources\Permission\PermissionResource;
use App\Models\Permission\Permission;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class PermissionService extends Service
{
    protected $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    public function paginate($request, $limit = 25)
    {
        $permissions = $this->permission
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where(
                    'name',
                    'like',
                    '%' . $request->name . '%'
                );
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('guard_name'), function ($query) use ($request) {
                $query->where('guard_name', $request->guard_name);
            })
            ->orderByDesc('id')
            ->paginate($limit);

        return PermissionResource::collection($permissions);
    }

    public function store($data)
    {
        try {

            return DB::connection('central')->transaction(function () use ($data) {

                $data['guard_name'] = $data['guard_name'] ?? 'web';

                return $this->permission->create($data);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = true)
    {
        $permission = $this->permission->find($id);

        if (!$permission) {
            return null;
        }

        return $resource
            ? new PermissionResource($permission)
            : $permission;
    }

    public function update($id, $data)
    {
        try {
            return DB::connection('central')->transaction(function () use ($id, $data) {

                $permission = $this->find($id, false);

                if (!$permission) {
                    return false;
                }

                if (!array_key_exists('guard_name', $data)) {
                    unset($data['guard_name']);
                }

                $permission->update($data);

                return $permission;
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            return DB::connection('central')->transaction(function () use ($id) {

                $permission = $this->find($id, false);

                if (!$permission) {
                    return false;
                }

                return $permission->delete();
            });
        } catch (\Exception $ex) {
            return false;
        }
    }
}
