<?php

namespace App\Models\Tenant\Purchase\Return;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant\Purchase\Return\Item\PurchaseReturnItem;
use App\Models\Tenant\Supplier\Supplier;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'purchase_returns';
    protected $fillable = [
        'supplier_id',
        'purchase_return_number',
        'returned_by',
        'remarks',
        'return_date',
        'return_miti',
        'sub_total',
        'tax',
        'total',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date',
        'return_miti' => 'date',
    ];

    protected $appends = ['status_text'];

    protected function getStatusTextAttribute()
    {
        return $this->status ? ucfirst($this->status) : null;
    }


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    protected function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id');
    }
}
