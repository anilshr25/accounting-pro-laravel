<?php

namespace App\Models\Tenant\Cheque;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'cheques';
    protected $fillable = [
        'bank_account_id',
        'party_type',
        'party_id',
        'type',
        'cheque_number',
        'amount',
        'date',
        'miti',
        'remarks',
        'status',
        'bank_name',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function party()
    {
        return $this->morphTo();
    }
}
