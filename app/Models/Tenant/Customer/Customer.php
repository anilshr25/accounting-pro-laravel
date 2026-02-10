<?php

namespace App\Models\Tenant\Customer;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Ledger\Ledger;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'customers';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'credit_balance',
        'vat',
    ];
    protected $appends = ['closing_balance'];

    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'party');
    }

    public function getClosingBalanceAttribute()
    {
        $latestLedger = $this->ledgers()
            ->orderBy('date', 'desc') 
            ->latest()
            ->first();

        if ($latestLedger) {
            return $latestLedger->balance;
        }

        return $this->credit_balance ?? 0;
    }
}
