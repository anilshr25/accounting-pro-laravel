<?php

namespace App\Models\Tenant\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant\Procurement\Item\ProcurementItem;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'product_name',
        'unit',
        'rate',
        'category',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {

            $lastCode = self::withTrashed()
                ->lockForUpdate()
                ->max('code');

            $product->code = $lastCode ? $lastCode + 1 : 01;
        });
    }

    public function procurementItems()
    {
        return $this->hasMany(ProcurementItem::class);
    }
}
