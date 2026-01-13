<?php

namespace App\Models\Tenant\PurchaseOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\PurchaseOrder\Item\PurchaseOrderItem;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'purchase_orders';
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
    protected $casts = [
        'order_date' => 'datetime',
        'received_date' => 'datetime',
    ];

    protected function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }
}
