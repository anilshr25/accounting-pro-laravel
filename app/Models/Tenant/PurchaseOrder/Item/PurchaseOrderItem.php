<?php

namespace App\Models\Tenant\PurchaseOrder\Item;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory, Auditable;
    protected $table = 'purchase_order_items';
    protected $fillable = [
        'purchase_order_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];
    public $timestamps = false;
}
