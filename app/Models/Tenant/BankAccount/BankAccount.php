<?php

namespace App\Models\Tenant\BankAccount;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bank_account';
    protected $fillable = [
        'bank_name',
        'account_number',
        'account_type',
        'balance',
    ];
}
