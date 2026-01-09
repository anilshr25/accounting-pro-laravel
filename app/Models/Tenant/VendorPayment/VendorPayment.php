<?php

namespace App\Models\Tenant\VendorPayment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'vendor_payments';
    protected $fillable = [
        'supplier_id',
        'date',
        'miti',
        'amount',
        'payment_type',
        'shift',
        'transaction_id',
        'remarks',
    ];
}
