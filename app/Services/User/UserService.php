<?php

namespace App\Services\User;

use App\Helpers\TenantAccessService;
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
    protected $tenantAccess;

    public function __construct(
        User $user,
        TenantAccessService $tenantAccess
    ) {
        $this->user = $user;
        $this->tenantAccess = $tenantAccess;
    }

    public function paginate($request, $limit = 25)
    {
        $tenantId = $this->tenantAccess->selectedTenantId($request);

        if (! $tenantId) {
            throw ValidationException::withMessages([
                'tenant_id' => [
                    'No business selected.'
                ],
            ]);
        }

        $this->tenantAccess->ensureCallerCanAccessTenant($tenantId);

        $users = $this->user
            ->with([
                'tenants.business',
                'tenantUsers.role.permissions',
            ])
            ->whereHas('tenantUsers', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
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

            $tenantId = $data['tenant_id'];

            $this->tenantAccess->ensureCallerCanAccessTenant(
                $tenantId
            );

            $tenantExists = DB::connection('central')
                ->table('tenants')
                ->where('id', $tenantId)
                ->exists();

            if (! $tenantExists) {
                throw ValidationException::withMessages([
                    'tenant_id' => ['The selected business does not exist.'],
                ]);
            }

            $roleName = trim($data['role']);

            $role = Role::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'name' => $roleName,
                ],
                [
                    'guard_name' => 'web',
                ]
            );

            if (array_key_exists('permission_ids', $data)) {

                $permissionIds = array_values(
                    array_unique($data['permission_ids'] ?? [])
                );

                $role->permissions()->sync($permissionIds);
            }

            $user = $this->user->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            TenantUser::create([
                'tenant_id' => $tenantId,
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

    public function find(
        $id,
        $resource = true,
        $request = null
    ) {
        if (! $request) {
            $request = request();
        }

        $tenantId = $this->tenantAccess->selectedTenantId($request);

        if (! $tenantId) {
            throw ValidationException::withMessages([
                'tenant_id' => [
                    'No business selected.'
                ],
            ]);
        }

        $this->tenantAccess->ensureCallerCanAccessTenant($tenantId);

        $user = $this->tenantAccess->ensureUserBelongsToTenant(
            $id,
            $tenantId
        );

        $user->load([
            'tenants.business',
            'tenantUsers.role.permissions',
        ]);

        return $resource
            ? new UserResource($user)
            : $user;
    }

    public function update($id, $data)
    {
        return DB::connection('central')->transaction(function () use ($id, $data) {

            $request = request();

            $tenantId = $this->tenantAccess->selectedTenantId($request);

            if (! $tenantId) {
                throw ValidationException::withMessages([
                    'tenant_id' => [
                        'No business selected.'
                    ],
                ]);
            }

            $this->tenantAccess->ensureCallerCanAccessTenant(
                $tenantId
            );

            $user = $this->tenantAccess->ensureUserBelongsToTenant(
                $id,
                $tenantId
            );

            $user->load([
                'tenants.business',
                'tenantUsers.role.permissions',
            ]);

            $roleName = trim($data['role']);
            $role = Role::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]
            );

            if (array_key_exists('permission_ids', $data)) {
                $permissionIds = array_values(array_unique($data['permission_ids'] ?? []));
                $role->permissions()->sync($permissionIds);
            }

            $isActive = $data['is_active'] ?? null;
            unset($data['tenant_id'], $data['role'], $data['permission_ids'], $data['is_active']);
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
            if (! empty($data)) {
                $user->update($data);
            }
            $tenantUser = TenantUser::query()
                ->where('user_id', $user->id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (! $tenantUser) {
                throw ValidationException::withMessages([
                    'tenant_id' => [
                        'User is not assigned to this business.'
                    ],
                ]);
            }

            $tenantUser->update([
                'role_id' => $role->id,
                'is_active' => $isActive ?? $tenantUser->is_active,
            ]);

            return $user->fresh([
                'tenants.business',
                'tenantUsers.role.permissions',
            ]);
        });
    }

    public function delete($id)
    {
        try {

            $request = request();

            $tenantId = $this->tenantAccess->selectedTenantId(
                $request
            );

            if (! $tenantId) {
                return false;
            }

            $this->tenantAccess->ensureCallerCanAccessTenant(
                $tenantId
            );

            $user = $this->tenantAccess->ensureUserBelongsToTenant(
                $id,
                $tenantId
            );

            return $user->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function assignBusiness($userId, array $data)
    {
        $request = request();

        $tenantId = $this->tenantAccess->selectedTenantId(
            $request
        );

        if (! $tenantId) {
            throw ValidationException::withMessages([
                'tenant_id' => [
                    'No business selected.'
                ],
            ]);
        }

        $this->tenantAccess->ensureCallerCanAccessTenant(
            $tenantId
        );

        if (
            isset($data['tenant_id']) &&
            $data['tenant_id'] !== $tenantId
        ) {
            throw ValidationException::withMessages([
                'tenant_id' => [
                    'The selected business does not match the current business.'
                ],
            ]);
        }

        $user = User::query()->find($userId);

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => [
                    'User does not exist.'
                ],
            ]);
        }

        $role = Role::query()
            ->where('id', $data['role_id'])
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $role) {
            throw ValidationException::withMessages([
                'role_id' => [
                    'The selected role does not belong to this business.'
                ],
            ]);
        }

        TenantUser::updateOrCreate(
            [
                'tenant_id' => $tenantId,
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

    public function updateBusinessAccess(
        $userId,
        $tenantId,
        $data
    ) {
        try {

            $request = request();

            $selectedTenantId =
                $this->tenantAccess->selectedTenantId($request);

            if (! $selectedTenantId) {
                return false;
            }

            if ($tenantId !== $selectedTenantId) {
                return false;
            }

            $this->tenantAccess->ensureCallerCanAccessTenant(
                $selectedTenantId
            );

            $user = $this->tenantAccess->ensureUserBelongsToTenant(
                $userId,
                $selectedTenantId
            );

            $role = Role::query()
                ->where('id', $data['role_id'])
                ->where('tenant_id', $selectedTenantId)
                ->first();

            if (! $role) {
                throw ValidationException::withMessages([
                    'role_id' => [
                        'The selected role does not belong to this business.'
                    ],
                ]);
            }

            $tenantUser = TenantUser::query()
                ->where('user_id', $user->id)
                ->where('tenant_id', $selectedTenantId)
                ->first();

            if (! $tenantUser) {
                return false;
            }

            $tenantUser->update([
                'role_id' => $data['role_id'],
                'is_active' => $data['is_active']
                    ?? $tenantUser->is_active,
            ]);

            return $user->fresh([
                'tenants.business',
                'tenantUsers.role.permissions',
            ]);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function removeBusiness(
        $userId,
        $tenantId
    ) {
        try {

            $request = request();

            $selectedTenantId =
                $this->tenantAccess->selectedTenantId($request);

            if (! $selectedTenantId) {
                return false;
            }

            if ($tenantId !== $selectedTenantId) {
                return false;
            }

            $this->tenantAccess->ensureCallerCanAccessTenant(
                $selectedTenantId
            );

            $user = $this->tenantAccess->ensureUserBelongsToTenant(
                $userId,
                $selectedTenantId
            );

            return $user->tenants()->detach(
                $selectedTenantId
            );
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function getBusinesses($userId)
    {
        $request = request();

        $tenantId = $this->tenantAccess->selectedTenantId(
            $request
        );

        if (! $tenantId) {
            throw ValidationException::withMessages([
                'tenant_id' => [
                    'No business selected.'
                ],
            ]);
        }

        $this->tenantAccess->ensureCallerCanAccessTenant(
            $tenantId
        );

        $user = $this->tenantAccess->ensureUserBelongsToTenant(
            $userId,
            $tenantId
        );

        $user->load([
            'tenants.business',
            'tenantUsers.role.permissions',
        ]);

        return new UserResource($user);
    }
}
