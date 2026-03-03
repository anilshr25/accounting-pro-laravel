<?php

namespace App\Models\Tenant\Procurement\Item;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant\Procurement\Procurement;
use App\Models\Tenant\Product\Product;

class ProcurementItem extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'procurement_id',
        'product_id',
        'quantity',
        'amount',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
