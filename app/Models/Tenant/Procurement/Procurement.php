<?php

namespace App\Models\Tenant\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Procurement extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'unit',
        'price',
        'category',
        'remarks',
        'status',
    ];

    protected $attributes = [
        'status' => 'received', 
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($procurement) {

            $lastCode = self::withTrashed()
                ->lockForUpdate()
                ->max('code');

            $procurement->code = $lastCode ? $lastCode + 1 : 01;
        });
    }
}
