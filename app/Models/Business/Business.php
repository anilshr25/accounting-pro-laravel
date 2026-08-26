<?php

namespace App\Models\Business;

use App\Models\OwnerUser\OwnerUser;
use App\Models\Tenant\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';

    protected $table = 'businesses';

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'status',
        'tenant_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(
            Tenant::class,
            'tenant_id',
            'id'
        );
    }

    public function owners()
    {
        return $this->belongsToMany(
            OwnerUser::class,
            'business_owners',
            'business_id',
            'owner_user_id'
        )
        ->withPivot('is_primary')
        ->withTimestamps();
    }

    public function primaryOwner(): BelongsToMany
    {
        return $this->belongsToMany(
            OwnerUser::class,
            'business_owners',
            'business_id',
            'owner_user_id'
        )
        ->wherePivot('is_primary', true)
        ->withPivot('is_primary')
        ->withTimestamps();
    }
}
