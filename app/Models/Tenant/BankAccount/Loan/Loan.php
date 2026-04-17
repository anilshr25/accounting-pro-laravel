<?php

namespace App\Models\Tenant\BankAccount\Loan;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\BankAccount\BankAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'duration_months',
        'loan_type',
        'emi_amount',
        'total_amount',
        'remaining_amount',
        'collateral',
        'repayment_schedule',
        'late_payment_charge',
        'start_date',
        'start_miti',
        'end_date',
        'end_miti',
        'status',
        'remarks',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
