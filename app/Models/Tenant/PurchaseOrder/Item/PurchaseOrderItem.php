<?php

namespace App\Models\Tenant\PurchaseOrder\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    protected $table = 'purchase_order_item';
    protected $fillable = [
        'purchase_order_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];
    public $timestamps = false;
}
