<?php

namespace App\Models\Tenant\Supplier;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Ledger\Ledger;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'suppliers';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'opening_balance',
        'closing_balance',
        'pan',
    ];

    protected $appends = ['closing_balance'];

    public function ledgers()
    {
        return $this->morphMany(Ledger::class, 'party');
    }

    public function getClosingBalanceAttribute()
{
    $totalCredit = $this->ledgers()->sum('credit');
    $totalDebit  = $this->ledgers()->sum('debit');

    return $this->opening_balance + $totalDebit - $totalCredit;
}

}
