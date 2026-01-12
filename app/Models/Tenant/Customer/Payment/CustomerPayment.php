<?php

namespace App\Models\Tenant\Customer\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'customer_payments';
    protected $fillable = [
        'customer_id',
        'date',
        'miti',
        'amount',
        'payment_type',
        'shift',
        'transaction_id',
        'remarks',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];
}
