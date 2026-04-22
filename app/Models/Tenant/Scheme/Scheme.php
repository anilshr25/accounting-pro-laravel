<?php

namespace App\Models\Tenant\Scheme;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Supplier\Supplier;
use App\Models\Tenant\Scheme\Payment\SchemePayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheme extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'schemes';
    protected $fillable = [
        'supplier_id',
        'scheme_name',
        'start_date',
        'start_miti',
        'end_date',
        'end_miti',
        'image',
        'issued_amount',
        'percentage',
        'scheme_type',
        'status',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'issued_amount' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(SchemePayment::class, 'scheme_id');
    }
}
