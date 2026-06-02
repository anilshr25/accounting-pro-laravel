<?php

namespace App\Models\Tenant\Notification;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'message',
        'loan_id',
        'payment_id',
        'is_read',
    ];
}
