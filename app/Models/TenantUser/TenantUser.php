<?php

namespace App\Models\TenantUser;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Tenant;
use App\Models\User\User;
use App\Models\Role\Role;

class TenantUser extends Model
{
    protected $connection = 'central';
    protected $table = 'tenant_user';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(
            Tenant::class,
            'tenant_id',
            'id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function role()
    {
        return $this->belongsTo(
            Role::class,
            'role_id',
            'id'
        );
    }
}
