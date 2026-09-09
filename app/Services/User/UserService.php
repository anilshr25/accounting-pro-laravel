<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Models\TenantUser\TenantUser;
use App\Services\Service;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Role\Role;
use Illuminate\Validation\ValidationException;

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
            ->with([
                'tenants.business',
                'tenantUsers.role.permissions',
            ])
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
        return DB::connection('central')->transaction(function () use ($data) {

            $tenantExists = DB::connection('central')
                ->table('tenants')
                ->where('id', $data['tenant_id'])
                ->exists();

            if (! $tenantExists) {
                throw ValidationException::withMessages([
                    'tenant_id' => ['The selected business does not exist.'],
                ]);
            }

            $role = Role::where('id', $data['role_id'])
                ->where('tenant_id', $data['tenant_id'])
                ->first();

            if (! $role) {
                throw ValidationException::withMessages([
                    'role_id' => [
                        'The selected role does not belong to this business.'
                    ],
                ]);
            }

            $user = $this->user->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            TenantUser::create([
                'tenant_id' => $data['tenant_id'],
                'user_id' => $user->id,
                'role_id' => $role->id,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $user->load([
                'tenants.business',
                'tenantUsers.role.permissions',
            ]);
        });
    }

    public function find($id, $resource = true)
    {
        $user = $this->user
            ->with([
                'tenants.business',
                'tenantUsers.role.permissions',
            ])
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
        return DB::connection('central')->transaction(function () use ($id, $data) {

            $user = $this->user
                ->with([
                    'tenants.business',
                    'tenantUsers.role.permissions',
                ])
                ->find($id);

            if (!$user) {
                return false;
            }

            $tenantId = $data['tenant_id'] ?? null;
            $roleId = $data['role_id'] ?? null;
            $permissionIds = $data['permission_ids'] ?? null;

            unset(
                $data['tenant_id'],
                $data['role_id'],
                $data['permission_ids']
            );

            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }

            if (!empty($data)) {
                $user->update($data);
            }

            if ($tenantId && $roleId !== null) {

                $tenantExists = DB::connection('central')
                    ->table('tenants')
                    ->where('id', $tenantId)
                    ->exists();

                if (!$tenantExists) {
                    throw ValidationException::withMessages([
                        'tenant_id' => [
                            'The selected business does not exist.'
                        ],
                    ]);
                }

                $role = Role::where('id', $roleId)
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (!$role) {
                    throw ValidationException::withMessages([
                        'role_id' => [
                            'The selected role does not belong to this business.'
                        ],
                    ]);
                }

                $tenantUser = TenantUser::where('user_id', $user->id)
                    ->where('tenant_id', $tenantId)
                    ->first();

                if (!$tenantUser) {
                    throw ValidationException::withMessages([
                        'tenant_id' => [
                            'User is not assigned to this business.'
                        ],
                    ]);
                }

                $tenantUser->update([
                    'role_id' => $roleId,
                    'is_active' => $data['is_active'] ?? $tenantUser->is_active,
                ]);

                if (is_array($permissionIds)) {
                    $role->permissions()->sync($permissionIds);
                }
            }

            return $user->fresh([
                'tenants.business',
                'tenantUsers.role.permissions',
            ]);
        });
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

        $role = Role::where('id', $data['role_id'])
            ->where('tenant_id', $data['tenant_id'])
            ->first();

        if (!$role) {
            throw ValidationException::withMessages([
                'role_id' => [
                    'The selected role does not belong to this business.'
                ],
            ]);
        }

        TenantUser::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'user_id' => $user->id,
            ],
            [
                'role_id' => $role->id,
                'is_active' => $data['is_active'] ?? true,
            ]
        );

        return $user->load([
            'tenants.business',
            'tenantUsers.role.permissions',
        ]);
    }

    public function updateBusinessAccess($userId, $tenantId, $data)
    {
        try {

            $user = $this->find($userId, false);

            if (!$user) {
                return false;
            }

            $role = Role::where('id', $data['role_id'])
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$role) {
                throw ValidationException::withMessages([
                    'role_id' => [
                        'The selected role does not belong to this business.'
                    ],
                ]);
            }

            $tenantUser = TenantUser::where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$tenantUser) {
                return false;
            }

            $tenantUser->update([
                'role_id' => $data['role_id'],
                'is_active' => $data['is_active'] ?? $tenantUser->is_active,
            ]);

            return $user->fresh([
                'tenants.business',
                'tenantUsers.role.permissions',
            ]);
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
            ->with([
                'tenants.business',
                'tenantUsers.role.permissions',
            ])
            ->find($userId);

        if (!$user) {
            return null;
        }

        return new UserResource($user);
    }
}
