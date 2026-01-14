<?php

namespace App\Models\AuditLog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'admin_user_id',
        'ip_address',
        'user_agent',
        'description',
    ];
}
