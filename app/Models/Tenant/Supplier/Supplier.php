<?php

namespace App\Models\Tenant\Supplier;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'suppliers';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'opening_balance',
        'closing_balance',
        'pan',
    ];
}
