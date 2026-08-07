<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Data\TenantMailAttachment;
use App\Exceptions\InfrastructureConfigurationException;
use App\Mail\Tenant\VerificationCodeMail;
use App\Services\Infrastructure\ScopedStorageService;
use App\Services\Infrastructure\TenantMailService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Mail\MailManager;
use Mockery;
use Tests\Support\ArrayLogger;
use Tests\Support\InfrastructureConfigurationFactory;
use Tests\Support\MutableInfrastructureConfigurationRepository;
use Tests\TestCase;

final class TenantMailServiceTest extends TestCase
{
    public function test_each_send_builds_an_isolated_mailer_and_applies_the_tenant_sender(): void
    {
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha'),
        );
        $logger = new ArrayLogger;
        $mailManager = Mockery::mock(MailManager::class);
        $mailer = Mockery::mock(Mailer::class);
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);
        $service = new TenantMailService($repository, $mailManager, $storage, $logger);

        $mailManager->shouldReceive('build')
            ->once()
            ->with(Mockery::on(fn (array $config): bool => $config['name'] === 'tenant-alpha-primary'
                && $config['host'] === 'smtp.alpha.test'
                && $config['username'] === 'user-alpha'
                && $config['password'] === 'password-alpha'
            ))
            ->andReturn($mailer);

        $mailer->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (VerificationCodeMail $mail): bool {
                return $mail->to[0]['address'] === 'recipient@example.test'
                    && $mail->from[0]['address'] === 'mail@alpha.test'
                    && $mail->from[0]['name'] === 'Tenant alpha';
            }));

        $service->send(
            'recipient@example.test',
            new VerificationCodeMail('Security code', '<p>Code</p>', '123456'),
        );

        $this->assertSame('alpha', $logger->records[0]['context']['tenant_id']);
    }

    public function test_configuration_changes_are_used_on_the_next_send_without_mutating_global_mail_config(): void
    {
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha'),
        );
        $logger = new ArrayLogger;
        $mailManager = Mockery::mock(MailManager::class);
        $mailer = Mockery::mock(Mailer::class);
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);
        $service = new TenantMailService($repository, $mailManager, $storage, $logger);
        $builtHosts = [];

        $mailManager->shouldReceive('build')
            ->twice()
            ->with(Mockery::on(function (array $config) use (&$builtHosts): bool {
                $builtHosts[] = $config['host'];

                return true;
            }))
            ->andReturn($mailer);
        $mailer->shouldReceive('send')->twice();

        $service->send('first@example.test', new VerificationCodeMail('Code', '<p>A</p>', '111111'));
        $repository->configuration = InfrastructureConfigurationFactory::tenant('bravo');
        $service->send('second@example.test', new VerificationCodeMail('Code', '<p>B</p>', '222222'));

        $this->assertSame(['smtp.alpha.test', 'smtp.bravo.test'], $builtHosts);
        $this->assertSame('array', config('mail.default'));
    }

    public function test_attachments_are_loaded_only_through_the_scoped_tenant_storage(): void
    {
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha'),
        );
        $logger = new ArrayLogger;
        $mailManager = Mockery::mock(MailManager::class);
        $mailer = Mockery::mock(Mailer::class);
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);
        $service = new TenantMailService($repository, $mailManager, $storage, $logger);
        $path = 'tests/invoice.txt';

        $storage->put($path, 'tenant alpha invoice');
        $mailManager->shouldReceive('build')->once()->andReturn($mailer);
        $mailer->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (VerificationCodeMail $mail): bool {
                return $mail->rawAttachments[0]['data'] === 'tenant alpha invoice'
                    && $mail->rawAttachments[0]['name'] === 'invoice.txt';
            }));

        $service->send(
            'recipient@example.test',
            new VerificationCodeMail('Invoice', '<p>Attached</p>', '123456'),
            [new TenantMailAttachment($path, 'invoice.txt', 'text/plain')],
        );

        $storage->delete($path);
    }

    public function test_primary_failure_uses_only_the_same_tenants_fallback_smtp(): void
    {
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('alpha', [
                'mailFallbackHost' => 'smtp-backup.alpha.test',
                'mailFallbackPort' => 465,
                'mailFallbackUsername' => 'backup-alpha',
                'mailFallbackPassword' => 'backup-secret',
                'mailFallbackEncryption' => 'ssl',
            ]),
        );
        $logger = new ArrayLogger;
        $mailManager = Mockery::mock(MailManager::class);
        $primaryMailer = Mockery::mock(Mailer::class);
        $fallbackMailer = Mockery::mock(Mailer::class);
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);
        $service = new TenantMailService($repository, $mailManager, $storage, $logger);

        $mailManager->shouldReceive('build')
            ->once()
            ->with(Mockery::on(fn (array $config): bool => $config['name'] === 'tenant-alpha-primary'))
            ->andReturn($primaryMailer);
        $primaryMailer->shouldReceive('send')->once()->andThrow(new \RuntimeException('Primary unavailable'));

        $mailManager->shouldReceive('build')
            ->once()
            ->with(Mockery::on(fn (array $config): bool => $config['name'] === 'tenant-alpha-fallback'
                && $config['host'] === 'smtp-backup.alpha.test'
                && $config['username'] === 'backup-alpha'
                && $config['password'] === 'backup-secret'
                && $config['scheme'] === 'smtps'
            ))
            ->andReturn($fallbackMailer);
        $fallbackMailer->shouldReceive('send')->once();

        $service->send(
            'recipient@example.test',
            new VerificationCodeMail('Code', '<p>A</p>', '111111'),
        );

        $this->assertSame('alpha', $logger->records[0]['context']['tenant_id']);
        $this->assertSame('smtp-fallback', $logger->records[1]['context']['provider']);
    }

    public function test_misconfigured_smtp_fails_closed_and_logs_the_tenant_id(): void
    {
        $repository = new MutableInfrastructureConfigurationRepository(
            InfrastructureConfigurationFactory::tenant('broken', ['mailPassword' => null]),
        );
        $logger = new ArrayLogger;
        $mailManager = Mockery::mock(MailManager::class);
        $storage = new ScopedStorageService($repository, app(FilesystemManager::class), $logger);
        $service = new TenantMailService($repository, $mailManager, $storage, $logger);

        $mailManager->shouldNotReceive('build');

        try {
            $service->send('recipient@example.test', new VerificationCodeMail('Code', '<p>A</p>', '111111'));
            $this->fail('Expected the SMTP configuration to be rejected.');
        } catch (InfrastructureConfigurationException) {
            $this->assertSame('broken', $logger->records[0]['context']['tenant_id']);
            $this->assertSame('smtp', $logger->records[0]['context']['provider']);
            $this->assertArrayNotHasKey('password', $logger->records[0]['context']);
        }
    }
}
