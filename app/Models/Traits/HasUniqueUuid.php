<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUniqueUuid
{
    public static function bootHasUniqueUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->uuid)) {
                do {
                    $uuid = (string) Str::uuid();
                } while ($model->newQuery()->where('uuid', $uuid)->exists());

                $model->uuid = $uuid;
            }
        });
    }
}
