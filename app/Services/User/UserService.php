<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Models\TenantUser\TenantUser;
use App\Services\Service;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService extends Service
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function paginate($request, $limit = 25)
    {
        $users = $this->user
            ->with('tenants.business')
            ->when($request->filled('name'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->name . '%')
                        ->orWhere('email', 'like', '%' . $request->name . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate($limit);

        return UserResource::collection($users);
    }

    public function store($data)
    {
        try {
            return DB::connection('central')->transaction(function () use ($data) {

                if (!empty($data['password'])) {
                    $data['password'] = Hash::make($data['password']);
                }

                return $this->user->create($data);
            });
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = true)
    {
        $user = $this->user
            ->with('tenants.business')
            ->find($id);

        if (!$user) {
            return null;
        }

        return $resource
            ? new UserResource($user)
            : $user;
    }

    public function update($id, $data)
    {
        try {

            $user = $this->find($id, false);

            if (!$user) {
                return false;
            }

            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return $user;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete($id)
    {
        try {

            $user = $this->find($id, false);

            if (!$user) {
                return false;
            }

            return $user->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function assignBusiness($userId, array $data)
    {
        $user = User::findOrFail($userId);

        TenantUser::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'user_id' => $user->id,
            ],
            [
                'role_id' => $data['role_id'],
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return $user->load('tenants.business');
    }

    public function updateBusinessAccess($userId, $tenantId, $data)
    {
        try {

            $user = $this->find($userId, false);

            if (!$user) {
                return false;
            }

            $exists = $user->tenants()
                ->where('tenants.id', $tenantId)
                ->exists();

            if (!$exists) {
                return false;
            }

            $user->tenants()->updateExistingPivot(
                $tenantId,
                [
                    'role_id' => $data['role_id'],
                    'is_active' => $data['is_active'] ?? true,
                ]
            );

            return $user;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function removeBusiness($userId, $tenantId)
    {
        try {

            $user = $this->find($userId, false);

            if (!$user) {
                return false;
            }

            return $user->tenants()->detach($tenantId);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function getBusinesses($userId)
    {
        $user = $this->user
            ->with('tenants.business')
            ->find($userId);

        if (!$user) {
            return null;
        }

        return new UserResource($user);
    }
}
