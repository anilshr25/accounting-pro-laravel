<?php

namespace App\Services\Role;

use App\Http\Resources\Role\RoleResource;
use App\Models\Role\Role;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class RoleService extends Service
{
    protected $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function paginate($request, $limit = 25)
    {
        $roles = $this->role
            ->with('permissions')
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where(
                    'name',
                    'like',
                    '%' . $request->name . '%'
                );
            })
            ->orderByDesc('id')
            ->paginate($limit);

        return RoleResource::collection($roles);
    }

    public function store($data)
    {
        try {
            return DB::connection('central')->transaction(function () use ($data) {

                $data['guard_name'] = $data['guard_name'] ?? 'web';

                return $this->role->create($data);
            });

        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = true)
    {
        $role = $this->role
            ->with('permissions')
            ->find($id);

        if (!$role) {
            return null;
        }

        return $resource
            ? new RoleResource($role)
            : $role;
    }

    public function update($id, $data)
    {
        try {

            $role = $this->find($id, false);

            if (!$role) {
                return false;
            }

            $role->update($data);

            return $role;

        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {

            $role = $this->find($id, false);

            if (!$role) {
                return false;
            }

            return $role->delete();

        } catch (\Exception $ex) {
            return false;
        }
    }

    public function assignPermissions($roleId, $permissionIds)
    {
        try {

            $role = $this->find($roleId, false);

            if (!$role) {
                return false;
            }

            $role->permissions()->sync($permissionIds);

            return $role->load('permissions');

        } catch (\Exception $ex) {
            return false;
        }
    }

    public function permissions($roleId)
    {
        $role = $this->role
            ->with('permissions')
            ->find($roleId);

        if (!$role) {
            return null;
        }

        return new RoleResource($role);
    }
}
