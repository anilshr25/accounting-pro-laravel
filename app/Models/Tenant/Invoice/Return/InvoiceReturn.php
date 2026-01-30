<?php

namespace App\Models\Tenant\Invoice\Return;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant\Customer\Customer;
use App\Models\Tenant\Invoice\Return\Item\InvoiceReturnItem;

class InvoiceReturn extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'invoice_returns';
    protected $fillable = [
        'customer_id',
        'sales_return_number',
        'remarks',
        'return_date',
        'return_miti',
        'shift',
        'sub_total',
        'tax',
        'total',
    ];

    protected $casts = [
        'return_date' => 'date',
        'return_miti' => 'date',
    ];

    protected $hidden = ['customer'];

    protected $appends = ['customer_name'];

    public function items()
    {
        return $this->hasMany(InvoiceReturnItem::class, 'invoice_return_id');
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer?->name;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    protected static function booted()
    {
        static::deleting(function ($invoiceReturn) {
            $invoiceReturn->items()->delete();
        });

        static::restoring(function ($invoiceReturn) {
            $invoiceReturn->items()->withTrashed()->restore();
        });
    }
}
