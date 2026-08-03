<?php

declare(strict_types=1);

namespace App\Data;

final readonly class InfrastructureConfiguration
{
    public function __construct(
        public string $scopeId,
        public bool $isTenant,
        public ?string $mailDriver,
        public ?string $mailHost,
        public ?int $mailPort,
        public ?string $mailUsername,
        public ?string $mailPassword,
        public ?string $mailEncryption,
        public ?string $mailSenderName,
        public ?string $mailSenderAddress,
        public ?string $mailFallbackHost,
        public ?int $mailFallbackPort,
        public ?string $mailFallbackUsername,
        public ?string $mailFallbackPassword,
        public ?string $mailFallbackEncryption,
        public string $storageType,
        public ?string $storageAccessKey,
        public ?string $storageSecretKey,
        public ?string $storageRegion,
        public ?string $storageEndpoint,
        public ?string $storageBucket,
        public ?string $storageUrl,
    ) {}

    public function storageRoot(): string
    {
        return $this->isTenant ? 'tenants/'.$this->scopeId : 'central';
    }
}
