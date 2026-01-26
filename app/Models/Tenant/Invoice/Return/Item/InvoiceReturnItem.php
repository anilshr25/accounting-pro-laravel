<?php

namespace App\Models\Tenant\Invoice\Return\Item;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReturnItem extends Model
{
    use HasFactory, Auditable;
    protected $table = 'invoice_return_items';
    protected $fillable = [
        'invoice_return_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];
    public $timestamps = false;
}
