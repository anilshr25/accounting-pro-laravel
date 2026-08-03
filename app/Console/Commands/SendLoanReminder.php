<?php

namespace App\Console\Commands;

use App\Models\Tenant\BankAccount\Loan\Payment\LoanPayment;
use App\Models\Tenant\Notification\Notification;
use App\Models\Tenant\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendLoanReminder extends Command
{
    protected $signature = 'loan:reminder {--days=3 : Days before the due date}';

    protected $description = 'Create isolated tenant loan payment reminders before their due date';

    public function handle(): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 365],
        ]);

        if ($days === false) {
            $this->error('The --days option must be between 0 and 365.');

            return self::FAILURE;
        }

        $targetDate = CarbonImmutable::today()->addDays($days)->toDateString();
        $failedTenants = 0;

        foreach (Tenant::query()->cursor() as $tenant) {
            try {
                $created = $tenant->run(function () use ($targetDate): int {
                    $count = 0;

                    LoanPayment::query()
                        ->whereDate('due_date', $targetDate)
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->each(function (LoanPayment $payment) use (&$count): void {
                            Notification::query()->firstOrCreate(
                                [
                                    'title' => 'Loan Payment Reminder',
                                    'loan_id' => $payment->loan_id,
                                    'payment_id' => $payment->getKey(),
                                ],
                                [
                                    'message' => "Your loan payment is due on {$payment->due_date->toDateString()}",
                                    'is_read' => false,
                                ],
                            );

                            $count++;
                        });

                    return $count;
                });

                $this->info("Tenant {$tenant->getTenantKey()}: {$created} reminder(s) processed.");
            } catch (Throwable $exception) {
                $failedTenants++;
                Log::error('Tenant loan reminder workflow failed.', [
                    'tenant_id' => (string) $tenant->getTenantKey(),
                    'target_date' => $targetDate,
                    'exception' => $exception::class,
                    'error' => $exception->getMessage(),
                ]);

                $this->error("Tenant {$tenant->getTenantKey()}: {$exception->getMessage()}");
            }
        }

        return $failedTenants === 0 ? self::SUCCESS : self::FAILURE;
    }
}
