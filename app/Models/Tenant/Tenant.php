<?php

namespace App\Models\Tenant;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'owner_user_id',
        'data',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
        'data' => 'array',
    ];

    /**
     * Get the domains for the tenant.
     */
    public function domains()
    {
        return $this->hasMany(\App\Models\Domain\Domain::class, 'tenant_id', 'id');
    }

    /**
     * Get the owner of the tenant.
     */
    public function owner()
    {
        return $this->belongsTo(\App\Models\OwnerUser\OwnerUser::class, 'owner_user_id', 'id');
    }
}
