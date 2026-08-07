<?php

namespace App\Models\Domain;

use App\Models\OwnerUser\OwnerUser;
use App\Models\Tenant\Tenant;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $connection = 'central';

    protected $table = 'domains';

    protected $fillable = [
        'domain',
        'tenant_id',
        'owner_user_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(OwnerUser::class, 'owner_user_id', 'id');
    }
}
