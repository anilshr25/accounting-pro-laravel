<?php

namespace App\Models\Tenant\BankAccount\Loan\Payment;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\BankAccount\Loan\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanPayment extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'loan_payments';
    protected $fillable = [
        'loan_id',
        'amount',
        'due_date',
        'paid_date',
        'paid_miti',
        'late_payment_charge',
        'status'
    ];
    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
