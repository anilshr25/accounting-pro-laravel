<?php

namespace App\Models\Domain;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';

    protected $fillable = [
        'domain',
        'tenant_id',
        'owner_user_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant\Tenant::class, 'tenant_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(\App\Models\OwnerUser\OwnerUser::class, 'owner_user_id', 'id');
    }
}
