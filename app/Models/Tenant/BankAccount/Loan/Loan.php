<?php

namespace App\Models\Tenant\BankAccount\Loan;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\BankAccount\BankAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\BankAccount\Loan\Payment\LoanPayment;

class Loan extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'loans';
    protected $fillable = [
        'bank_account_id',
        'loan_number',
        'principal_amount',
        'premium_rate',
        'base_rate',
        'duration',
        'loan_type',
        'emi_amount',
        'total_amount',
        'remaining_amount',
        'payment_type',
        'collateral',
        'start_date',
        'start_miti',
        'end_date',
        'end_miti',
        'status',
        'remarks',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class, 'loan_id');
    }

    public function nextPayment()
    {
        return $this->hasOne(LoanPayment::class, 'loan_id')
            ->orderBy('due_date', 'asc');
    }
}
