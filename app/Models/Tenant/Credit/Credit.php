<?php

namespace App\Models\Tenant\Credit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;
    protected $table = 'credit';
    protected $fillable = [
        'type',
        'amount',
        'return_amount',
        'description',
        'date',
        'miti',
        'shift',
        'status',
        'customer_id',
        'supplier_id',
    ];
    public $timestamps = false;
}
