<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Model;
use App\Models\Permission\Permission;

class Role extends Model
{
    protected $connection = 'central';

    protected $table = 'roles';
    
    protected $fillable = [
        'name',
        'guard_name',
    ];

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
