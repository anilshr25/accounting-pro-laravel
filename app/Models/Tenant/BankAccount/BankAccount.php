<?php

namespace App\Models\Tenant\BankAccount;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'bank_accounts';
    protected $fillable = [
        'bank_name',
        'account_number',
        'account_type',
        'balance',
    ];
}
