<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Mail\Tenant\VerificationCodeMail;
use App\Models\Tenant\Notification\Notification;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\User\User;
use App\Services\Authenticator\Authenticator;
use App\Services\Infrastructure\TenantMailService;
use App\Services\Traits\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendVerificationCodeEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use MailTemplate;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 45;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly string $tenantId,
        public readonly int $userId,
    ) {
        $this->onQueue('tenant-mail');
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30, 120];
    }

    public function handle(Authenticator $authenticator, TenantMailService $mail): void
    {
        $this->runInTenant(function () use ($authenticator, $mail): void {
            $user = User::query()->findOrFail($this->userId);
            $secret = filled($user->mfa_secret_code) ? $user->mfa_secret_code : $user->email;
            $code = $authenticator->getCode($secret);
            $template = $this->getTemplate('user', 'verification_code_email');
            $content = $this->sanitize([
                'first_name' => $user->first_name,
                'verification_code' => $code,
            ], $template);

            $mail->send(
                $user->email,
                new VerificationCodeMail(
                    subjectLine: $template->subject,
                    htmlContent: $content,
                    verificationCode: $code,
                ),
            );
        });
    }

    public function failed(?Throwable $exception): void
    {
        try {
            $this->runInTenant(function (): void {
                Notification::query()->create([
                    'title' => 'Email delivery failed',
                    'message' => 'A verification email could not be delivered after all retry attempts.',
                    'is_read' => false,
                ]);
            });
        } catch (Throwable $notificationException) {
            Log::critical('Tenant email failure notification could not be stored.', [
                'tenant_id' => $this->tenantId,
                'exception' => $notificationException::class,
                'error' => $notificationException->getMessage(),
            ]);
        }

        Log::error('Tenant verification email exhausted all retries.', [
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'exception' => $exception?->getMessage(),
        ]);
    }

    private function runInTenant(callable $callback): mixed
    {
        if (tenancy()->initialized && (string) tenant()->getTenantKey() === $this->tenantId) {
            return $callback();
        }

        $tenant = Tenant::query()->findOrFail($this->tenantId);

        return $tenant->run($callback);
    }
}
