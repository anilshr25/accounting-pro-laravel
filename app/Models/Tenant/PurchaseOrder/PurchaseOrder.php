<?php

namespace App\Models\Tenant\PurchaseOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'purchase_order';
    protected $fillable = [
        'supplier_id',
        'purchase_invoice_number',
        'order_date',
        'received_date',
        'tax',
        'sub_total',
        'total',
        'status',
        'received_by',
    ];
}
