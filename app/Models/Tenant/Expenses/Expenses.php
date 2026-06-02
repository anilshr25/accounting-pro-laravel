<?php

namespace App\Models\Tenant\Expenses;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expenses extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'expenses';

    protected $fillable = [
        'title',
        'expense_type',
        'expense_date',
        'expense_miti',
        'amount',
        'payment_method',
        'description',
        'status',
    ];
    protected $casts = [
        'expense_date' => 'date',
    ];
}
