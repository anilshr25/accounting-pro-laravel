<?php

namespace App\Models\Tenant\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant\Procurement\Item\ProcurementItem;

class Procurement extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'order_number',
        'order_date',
        'order_time',
        'order_miti',
        'order_created_by',
        'total_amount',
        'status',
        'remarks',
    ];

    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'order_miti' => 'date:Y-m-d',
        'order_time' => 'datetime:H:i:s',
    ];

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (is_null($model->order_time)) {
                $model->order_time = now()->timezone('Asia/Kathmandu');
            }
        });
    }
}
