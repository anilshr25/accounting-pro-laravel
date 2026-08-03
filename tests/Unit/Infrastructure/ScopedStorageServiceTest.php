<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Exceptions\InfrastructureConfigurationException;
use App\Services\Infrastructure\ScopedStorageService;
use Illuminate\Filesystem\FilesystemManager;
use Tests\Support\ArrayLogger;
use Tests\Support\InfrastructureConfigurationFactory;
use Tests\Support\MutableInfrastructureConfigurationRepository;
use Tests\TestCase;

final class ScopedStorageServiceTest extends TestCase
{
    public function test_local_files_with_the_same_path_are_isolated_between_tenants(): void
    {
        $logger = new ArrayLogger;
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha'),
        );
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);
        $path = 'tests/isolation.txt';

        $storage->put($path, 'alpha contents');
        $repository->configuration = InfrastructureConfigurationFactory::tenant('bravo');
        $storage->put($path, 'bravo contents');

        $this->assertSame('bravo contents', $storage->get($path));

        $repository->configuration = InfrastructureConfigurationFactory::tenant('alpha');
        $this->assertSame('alpha contents', $storage->get($path));

        $storage->delete($path);
        $repository->configuration = InfrastructureConfigurationFactory::tenant('bravo');
        $storage->delete($path);
    }

    public function test_s3_configuration_is_scoped_and_differs_for_each_tenant(): void
    {
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha'),
        );
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), new ArrayLogger);
        $alpha = InfrastructureConfigurationFactory::tenant('alpha', [
            'storageType' => 'aws',
            'storageAccessKey' => 'alpha-key',
            'storageSecretKey' => 'alpha-secret',
            'storageRegion' => 'us-east-1',
            'storageBucket' => 'alpha-bucket',
        ]);
        $bravo = InfrastructureConfigurationFactory::tenant('bravo', [
            'storageType' => 'wasabi',
            'storageAccessKey' => 'bravo-key',
            'storageSecretKey' => 'bravo-secret',
            'storageRegion' => 'eu-central-2',
            'storageBucket' => 'bravo-bucket',
        ]);

        $alphaDisk = $storage->diskConfiguration($alpha);
        $bravoDisk = $storage->diskConfiguration($bravo);

        $this->assertSame('alpha-bucket', $alphaDisk['bucket']);
        $this->assertSame('tenants/alpha', $alphaDisk['root']);
        $this->assertSame('bravo-bucket', $bravoDisk['bucket']);
        $this->assertSame('tenants/bravo', $bravoDisk['root']);
        $this->assertNotSame($alphaDisk['key'], $bravoDisk['key']);
        $this->assertTrue($alphaDisk['throw']);
        $this->assertSame('private', $bravoDisk['visibility']);
    }

    public function test_misconfigured_s3_write_fails_closed_and_logs_without_credentials(): void
    {
        $logger = new ArrayLogger;
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('broken', [
                'storageType' => 'aws',
                'storageAccessKey' => 'should-not-be-logged',
                'storageSecretKey' => null,
                'storageRegion' => 'us-east-1',
                'storageBucket' => 'broken-bucket',
            ]),
        );
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);

        try {
            $storage->put('document.pdf', 'contents');
            $this->fail('Expected the storage configuration to be rejected.');
        } catch (InfrastructureConfigurationException) {
            $record = $logger->records[0];

            $this->assertSame('broken', $record['context']['tenant_id']);
            $this->assertSame('aws', $record['context']['provider']);
            $this->assertSame('broken-bucket', $record['context']['bucket']);
            $this->assertArrayNotHasKey('key', $record['context']);
            $this->assertArrayNotHasKey('secret', $record['context']);
        }
    }

    public function test_path_traversal_is_rejected_and_logged(): void
    {
        $logger = new ArrayLogger;
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha'),
        );
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);

        $this->expectException(InfrastructureConfigurationException::class);

        try {
            $storage->get('../bravo/secret.txt');
        } finally {
            $this->assertSame('alpha', $logger->records[0]['context']['tenant_id']);
            $this->assertSame('read', $logger->records[0]['context']['operation']);
        }
    }
}
