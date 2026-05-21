<?php

namespace App\Models\Tenant\Scheme\Payment;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Scheme\Scheme;

class SchemePayment extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'scheme_payments';
    protected $fillable = [
        'scheme_id',
        'amount',
        'date',
        'miti',
        'remarks',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}
