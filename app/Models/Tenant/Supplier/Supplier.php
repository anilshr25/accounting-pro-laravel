<?php

namespace App\Models\Tenant\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'supplier';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'opening_balance',
        'pan',
    ];
}
