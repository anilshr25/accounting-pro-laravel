<?php

namespace App\Models\Tenant\PurchaseOrder;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Supplier\Supplier;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant\PurchaseOrder\Item\PurchaseOrderItem;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, Auditable;
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    protected function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }
}
