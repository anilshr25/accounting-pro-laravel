<?php

namespace App\Services\Tenant\User;

use App\Models\Tenant\User\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Tenant\User\UserResource;

class UserService
{
    protected User $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function paginate($request, $limit = 25)
    {
        $user = $this->user
        ->when($request->filled('info'), function ($query) use ($request) {
            $query->where(function ($sub) use ($request) {
                $info = $request->info;
                $sub->where('first_name', 'like', "%{$info}%")
                    ->orWhere('last_name', 'like', "%{$info}%")
                    ->orWhere('email', 'like', "%{$info}%")
                    ->orWhere('phone', 'like', "%{$info}%");
            });
        })
            ->when($request->filled('company_name'), function ($query) use ($request) {
                $query->where('company_name', 'like', "%{$request->company_name}%");
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy('id', 'DESC')
            ->paginate($request->limit ?? $limit);
        return UserResource::collection($user);
    }

    public function getUserForLogin(array $credentials)
    {
        $user = $this->user->whereEmail($credentials['email'])->first();
        if (empty($user))
            return null;

        if (Hash::check($credentials['password'], $user->password))
            return $user;

        return null;
    }

    public function store(array $data): User|bool
    {
        try {
            $user = $this->user->create($data);
            return $user;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function find($id, $resource = false)
    {
        $user = $this->user->find($id);
        if (!$user) {
            return null;
        }
        return $resource ? new UserResource($user) : $user;
    }

    public function update(string $uuid, array $data): bool
    {
        try {
            $user = $this->user->find($uuid);
            if (!$user) {
                return false;
            }
            return $user->update($data);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function delete(string $uuid): bool
    {
        try {
            $user = $this->user->find($uuid);
            if (!$user) {
                return false;
            }
            return (bool) $user->delete();
        } catch (\Exception $ex) {
            return false;
        }
    }
}
