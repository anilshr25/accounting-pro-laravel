<?php

namespace App\Models\Tenant\Kye\EmergencyContact;

use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Kye\Kye;

class KyeEmergencyContact extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kye_emergency_contacts';
    protected $fillable = [
        'kye_id',
        'name',
        'relationship',
        'phone',
        'email',
    ];
    public function kye()
    {
        return $this->belongsTo(Kye::class);
    }
}
