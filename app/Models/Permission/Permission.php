<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Model;
use App\Models\Role\Role;

class Permission extends Model
{
    protected $connection = 'central';

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'type',
        'guard_name',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions'
        );
    }
}
