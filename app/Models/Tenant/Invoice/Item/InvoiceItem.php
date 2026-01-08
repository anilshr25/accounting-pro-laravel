<?php

namespace App\Models\Tenant\Invoice\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $table = 'invoice_item';
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'rate',
        'amount',
    ];
    public $timestamps = false;
}
