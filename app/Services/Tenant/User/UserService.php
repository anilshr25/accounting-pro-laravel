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

    public function paginate(mixed $request, int $limit = 25)
    {
        $user = $this->user->query()
            ->when($request->filled('info'), function ($query) use ($request) {
                $query->where('info', 'like', '%' . $request->info . '%');
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->whereIsActive($request->is_active);
            })
            ->orderBy('id', 'DESC')
            ->paginate($limit);
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

    public function find(string $uuid): ?UserResource
    {
        $user = $this->user->where('uuid', $uuid)->first();
        if (!empty($user))
            return new UserResource($user);
        return null;
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

