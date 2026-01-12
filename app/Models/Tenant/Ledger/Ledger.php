<?php

namespace App\Models\Tenant\Ledger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ledgers';

    protected $fillable = [
        'date',
        'party_type',
        'party_id',
        'debit',
        'credit',
        'reference_type',
        'reference_id',
        'remarks',
        'balance',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function party()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
