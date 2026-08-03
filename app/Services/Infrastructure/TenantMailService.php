<?php

declare(strict_types=1);

namespace App\Services\Infrastructure;

use App\Contracts\InfrastructureConfigurationRepository;
use App\Data\InfrastructureConfiguration;
use App\Data\TenantMailAttachment;
use App\Exceptions\InfrastructureConfigurationException;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\MailManager;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class TenantMailService
{
    public function __construct(
        private InfrastructureConfigurationRepository $configurations,
        private MailManager $mailManager,
        private ScopedStorageService $storage,
        private LoggerInterface $logger,
    ) {}

    /** @param list<TenantMailAttachment> $attachments */
    public function send(string $recipient, Mailable $mailable, array $attachments = []): void
    {
        $configuration = $this->configurations->current();

        try {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new InfrastructureConfigurationException('A valid email recipient is required.');
            }

            $mailable
                ->to($recipient)
                ->from($configuration->mailSenderAddress, $configuration->mailSenderName);

            foreach ($attachments as $attachment) {
                if (! $attachment instanceof TenantMailAttachment) {
                    throw new InfrastructureConfigurationException('Tenant email attachments must be scoped attachment objects.');
                }

                $mailable->attachData(
                    $this->storage->get($attachment->path),
                    $attachment->name,
                    array_filter(['mime' => $attachment->mimeType]),
                );
            }

            try {
                $this->mailManager
                    ->build($this->mailerConfiguration($configuration))
                    ->send($mailable);

                $provider = 'smtp-primary';
            } catch (Throwable $primaryException) {
                $fallbackConfig = $this->fallbackMailerConfiguration($configuration);

                if ($fallbackConfig === null) {
                    throw $primaryException;
                }

                $this->logger->warning('Tenant primary SMTP failed; attempting tenant fallback SMTP.', [
                    'tenant_id' => $configuration->scopeId,
                    'mail_type' => $mailable::class,
                    'primary_host' => $configuration->mailHost,
                    'exception' => $primaryException::class,
                    'error' => $primaryException->getMessage(),
                ]);

                $this->mailManager->build($fallbackConfig)->send($mailable);
                $provider = 'smtp-fallback';
            }

            $this->logger->info('Tenant email delivered.', [
                'tenant_id' => $configuration->scopeId,
                'mail_type' => $mailable::class,
                'recipient_domain' => $this->recipientDomain($recipient),
                'provider' => $provider,
            ]);
        } catch (Throwable $exception) {
            $this->logger->error('Tenant email delivery failed.', [
                'tenant_id' => $configuration->scopeId,
                'mail_type' => $mailable::class,
                'recipient_domain' => $this->recipientDomain($recipient),
                'provider' => $configuration->mailDriver,
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /** @return array<string, mixed> */
    public function mailerConfiguration(InfrastructureConfiguration $configuration): array
    {
        if (! $configuration->isTenant) {
            throw new InfrastructureConfigurationException('Tenant email cannot be sent outside a tenant context.');
        }

        if (($configuration->mailDriver ?? 'smtp') !== 'smtp') {
            throw new InfrastructureConfigurationException("Unsupported tenant mail driver [{$configuration->mailDriver}].");
        }

        if ($configuration->mailHost === null || $configuration->mailPort === null) {
            throw new InfrastructureConfigurationException('Tenant SMTP host and port are required.');
        }

        if ($configuration->mailUsername === null || $configuration->mailPassword === null) {
            throw new InfrastructureConfigurationException('Tenant SMTP username and password are required.');
        }

        if ($configuration->mailSenderAddress === null || ! filter_var($configuration->mailSenderAddress, FILTER_VALIDATE_EMAIL)) {
            throw new InfrastructureConfigurationException('A valid tenant sender address is required.');
        }

        return $this->smtpConfiguration(
            name: 'tenant-'.$configuration->scopeId.'-primary',
            host: $configuration->mailHost,
            port: $configuration->mailPort,
            username: $configuration->mailUsername,
            password: $configuration->mailPassword,
            encryption: $configuration->mailEncryption,
        );
    }

    /** @return array<string, mixed>|null */
    public function fallbackMailerConfiguration(InfrastructureConfiguration $configuration): ?array
    {
        $values = [
            $configuration->mailFallbackHost,
            $configuration->mailFallbackPort,
            $configuration->mailFallbackUsername,
            $configuration->mailFallbackPassword,
        ];

        if (count(array_filter($values, static fn (mixed $value): bool => $value !== null)) === 0) {
            return null;
        }

        if (in_array(null, $values, true)) {
            throw new InfrastructureConfigurationException('Tenant fallback SMTP configuration is incomplete.');
        }

        return $this->smtpConfiguration(
            name: 'tenant-'.$configuration->scopeId.'-fallback',
            host: $configuration->mailFallbackHost,
            port: $configuration->mailFallbackPort,
            username: $configuration->mailFallbackUsername,
            password: $configuration->mailFallbackPassword,
            encryption: $configuration->mailFallbackEncryption,
        );
    }

    /** @return array<string, mixed> */
    private function smtpConfiguration(
        string $name,
        string $host,
        int $port,
        string $username,
        string $password,
        ?string $encryption,
    ): array {
        $scheme = in_array($encryption, ['ssl', 'smtps'], true) ? 'smtps' : 'smtp';

        return [
            'name' => $name,
            'transport' => 'smtp',
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'timeout' => 15,
        ];
    }

    private function recipientDomain(string $recipient): string
    {
        return str_contains($recipient, '@') ? (string) str($recipient)->afterLast('@') : 'invalid';
    }
}
