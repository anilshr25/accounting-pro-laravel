<?php

declare(strict_types=1);

namespace App\Services\Infrastructure;

use App\Contracts\InfrastructureConfigurationRepository;
use App\Data\InfrastructureConfiguration;
use App\Exceptions\InfrastructureConfigurationException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ScopedStorageService
{
    public function __construct(
        private InfrastructureConfigurationRepository $configurations,
        private FilesystemManager $filesystemManager,
        private LoggerInterface $logger,
    ) {}

    public function filesystem(): Filesystem
    {
        return $this->filesystemManager->build($this->diskConfiguration($this->configurations->current()));
    }

    /** @return array<string, mixed> */
    public function diskConfiguration(InfrastructureConfiguration $configuration): array
    {
        if ($configuration->storageType === 'local') {
            return [
                'driver' => 'local',
                'root' => storage_path('app/private/managed/'.$configuration->storageRoot()),
                'visibility' => 'private',
                'throw' => true,
                'report' => true,
            ];
        }

        if (! in_array($configuration->storageType, ['aws', 'wasabi'], true)) {
            throw new InfrastructureConfigurationException("Unsupported storage provider [{$configuration->storageType}].");
        }

        foreach (['storageAccessKey', 'storageSecretKey', 'storageRegion', 'storageBucket'] as $field) {
            if ($configuration->{$field} === null) {
                throw new InfrastructureConfigurationException("Missing required tenant storage setting [{$field}].");
            }
        }

        $endpoint = $configuration->storageEndpoint;
        if ($endpoint === null && $configuration->storageType === 'wasabi') {
            $endpoint = "https://s3.{$configuration->storageRegion}.wasabisys.com";
        }

        return [
            'driver' => 's3',
            'key' => $configuration->storageAccessKey,
            'secret' => $configuration->storageSecretKey,
            'region' => $configuration->storageRegion,
            'bucket' => $configuration->storageBucket,
            'url' => $configuration->storageUrl,
            'endpoint' => $endpoint,
            'root' => $configuration->storageRoot(),
            'visibility' => 'private',
            'throw' => true,
            'report' => true,
            'use_path_style_endpoint' => false,
        ];
    }

    public function put(string $path, string $contents): void
    {
        $configuration = $this->configurations->current();

        try {
            $written = $this->filesystemManager
                ->build($this->diskConfiguration($configuration))
                ->put($this->normalizePath($path), $contents, ['visibility' => 'private']);

            if (! $written) {
                throw new InfrastructureConfigurationException('The storage provider rejected the file write.');
            }
        } catch (Throwable $exception) {
            $this->logger->error('Scoped file write failed.', [
                'tenant_id' => $configuration->scopeId,
                'provider' => $configuration->storageType,
                'bucket' => $configuration->storageBucket,
                'path' => $path,
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function get(string $path): string
    {
        $configuration = $this->configurations->current();

        try {
            return $this->filesystemManager
                ->build($this->diskConfiguration($configuration))
                ->get($this->normalizePath($path));
        } catch (Throwable $exception) {
            $this->logFailure('read', $configuration, $path, $exception);

            throw $exception;
        }
    }

    public function delete(array|string $paths): bool
    {
        $configuration = $this->configurations->current();

        try {
            $paths = array_map($this->normalizePath(...), (array) $paths);

            if ($paths === []) {
                return true;
            }

            return $this->filesystemManager
                ->build($this->diskConfiguration($configuration))
                ->delete($paths);
        } catch (Throwable $exception) {
            $this->logFailure('delete', $configuration, implode(',', (array) $paths), $exception);

            throw $exception;
        }
    }

    public function temporaryUrl(string $path, int $minutes = 10): string
    {
        $configuration = $this->configurations->current();

        try {
            return URL::temporarySignedRoute(
                $configuration->isTenant ? 'tenant.storage.show' : 'central.storage.show',
                now()->addMinutes(max(1, min($minutes, 60))),
                ['file' => Crypt::encryptString($this->normalizePath($path))],
            );
        } catch (Throwable $exception) {
            $this->logFailure('temporary_url', $configuration, $path, $exception);

            throw $exception;
        }
    }

    public function url(string $path): string
    {
        return $this->temporaryUrl($path);
    }

    public function mimeType(string $path): string
    {
        $configuration = $this->configurations->current();

        try {
            return $this->filesystemManager
                ->build($this->diskConfiguration($configuration))
                ->mimeType($this->normalizePath($path));
        } catch (Throwable $exception) {
            $this->logFailure('mime_type', $configuration, $path, $exception);

            throw $exception;
        }
    }

    private function normalizePath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, '../')) {
            throw new InfrastructureConfigurationException('Invalid scoped storage path.');
        }

        return $path;
    }

    private function logFailure(
        string $operation,
        InfrastructureConfiguration $configuration,
        string $path,
        Throwable $exception,
    ): void {
        $this->logger->error('Scoped storage operation failed.', [
            'tenant_id' => $configuration->scopeId,
            'operation' => $operation,
            'provider' => $configuration->storageType,
            'bucket' => $configuration->storageBucket,
            'path' => $path,
            'exception' => $exception::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
