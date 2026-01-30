<?php

namespace App\Models\Tenant\Purchase\Order\Item;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes, Auditable;
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
