<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;
use App\Models\Permission\Permission;
use App\Models\Tenant\Tenant;

class Role extends Model
{
    protected $connection = 'central';

    protected $table = 'roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'guard_name',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        );
    }
}
