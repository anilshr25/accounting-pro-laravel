<?php

namespace App\Models\Tenant\Cheque;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\BankAccount\BankAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    protected $appends = ['status_text'];

    protected function getStatusTextAttribute()
    {
        return $this->status ? ucfirst($this->status) : null;
    }

    public function party()
    {
        return $this->morphTo();
    }

    public function bank_account()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
