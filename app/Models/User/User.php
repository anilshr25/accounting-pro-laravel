<?php

namespace App\Models\User;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Tenant\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\TenantUser\TenantUser;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $connection = 'central';
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'tenant_user',
            'user_id',
            'tenant_id'
        )
            ->withPivot([
                'role_id',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function tenantUsers()
    {
        return $this->hasMany(
            TenantUser::class,
            'user_id',
            'id'
        );
    }
}
