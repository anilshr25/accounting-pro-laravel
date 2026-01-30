<?php

namespace App\Models\Tenant\Invoice;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Customer\Customer;
use App\Models\Tenant\Invoice\Item\InvoiceItem;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'invoices';
    protected $fillable = [
        'customer_id',
        'invoice_miti',
        'invoice_date',
        'tax',
        'sub_total',
        'total',
        'payment_type',
        'status',
        'remarks',
        'shift',
        'sale_return',
    ];
    protected $casts = [
        'invoice_date' => 'datetime',
        'sale_return' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    protected static function booted()
    {
        static::deleting(function ($invoice) {
            $invoice->items()->delete();
        });

        static::restoring(function ($invoice) {
            $invoice->items()->withTrashed()->restore();
        });
    }
}
