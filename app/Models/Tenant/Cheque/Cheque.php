<?php

namespace App\Models\Tenant\Cheque;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'cheques';
    protected $fillable = [
        'bank_account_id',
        'supplier_id',
        'customer_id',
        'type',
        'cheque_number',
        'pay_to',
        'amount',
        'date',
        'miti',
        'remarks',
        'status',
        'bank_name',
    ];
}
