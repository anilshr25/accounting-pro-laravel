<?php

namespace App\Models\Tenant\Ledger;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use HasFactory, SoftDeletes, Auditable;

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

    public function getFiscalYearAttribute(): ?string
    {
        $miti = $this->getRawMiti();

        if (!$miti) {
            return null;
        }

        try {
            [$year, $month] = explode('-', $miti);

            $year = (int) $year;
            $month = (int) $month;

            return $month >= 4
                ? $year . '/' . ($year + 1)
                : ($year - 1) . '/' . $year;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getRawMiti(): ?string
    {
        return match ($this->reference_type) {
            'invoice_return'  => $this->reference?->return_miti,
            'purchase_return' => $this->reference?->return_miti,
            'invoice'         => $this->reference?->invoice_miti,
            'purchase_order'  => $this->reference?->received_date_miti,
            'cheque'          => $this->reference?->miti,
            'credit'          => $this->reference?->miti,
            'payment'         => $this->reference?->miti,
            default           => null,
        };
    }
}
