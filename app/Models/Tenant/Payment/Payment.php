<?php

namespace App\Models\Tenant\Payment;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'payments';

    protected $fillable = [
        'party_type',
        'party_id',
        'date',
        'miti',
        'amount',
        'payment_method',
        'shift',
        'transaction_id',
        'is_posted',
        'remarks',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function party()
    {
        return $this->morphTo();
    }

    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'reference');
    }
}
