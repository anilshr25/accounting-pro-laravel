<?php

namespace App\Models\Tenant\Invoice\Item;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'invoice_items';
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];
    public $timestamps = false;
}
