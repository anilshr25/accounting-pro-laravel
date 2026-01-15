<?php

namespace App\Models\Tenant\Credit;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Credit extends Model
{
    use HasFactory;
    protected $table = 'credits';
    protected $fillable = [
        'customer_id',
        'date',
        'miti',
        'shift',
        'type',
        'invoice_no',
        'amount',
        'return_amount',
        'description',
        'status',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
