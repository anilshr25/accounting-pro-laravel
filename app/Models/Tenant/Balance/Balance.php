<?php

namespace App\Models\Tenant\Balance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Balance extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'balances';
    protected $fillable = [
        'date',
        'opening_balance',
        'closing_balance',
        'shift',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];
}
