<?php

namespace App\Models\Tenant\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'invoice';
    protected $fillable = [
        'customer_id',
        'invoice_miti',
        'invoice_date',
        'tax',
        'sub_total',
        'total',
        'payment_type',
        'status',
        'remarks',
        'shift',
        'sale_return',
    ];
}
