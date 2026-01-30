<?php

namespace App\Models\Tenant\Purchase\Return\Item;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'purchase_return_items';
    protected $fillable = [
        'purchase_return_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];
    public $timestamps = false;
}
