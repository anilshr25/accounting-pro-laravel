<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TenantMailAttachment
{
    public function __construct(
        public string $path,
        public string $name,
        public ?string $mimeType = null,
    ) {}
}
