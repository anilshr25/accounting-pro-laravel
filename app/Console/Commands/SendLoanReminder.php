<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoanReminderMail;
use App\Models\Tenant\BankAccount\Loan\Payment\LoanPayment;

class SendLoanReminder extends Command
{
    protected $signature = 'loan:reminder';
    protected $description = 'Send loan reminder emails before 3 days of due date';

    public function handle()
    {
        $targetDate = Carbon::today()->addDays(3)->toDateString();

        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $tenant) {

            $data = json_decode($tenant->data, true);
            $dbName = $data['tenancy_db_name'] ?? null;

            if (!$dbName) {
                $this->error("No DB found for tenant ID {$tenant->id}");
                continue;
            }

            config(['database.connections.mysql.database' => $dbName]);
            DB::purge('mysql');
            DB::reconnect('mysql');

            $payments = LoanPayment::with('loan')
                ->whereDate('due_date', $targetDate)
                ->whereNull('paid_date')
                ->get();

            if ($payments->isEmpty()) {
                $this->info("No payments found for {$dbName}");
                continue;
            }

            foreach ($payments as $payment) {

                if ($payment->paid_date) {
                    continue;
                }

                $email = config('loan.reminder_email') ?? config('mail.from.address');

                if (!$email) {
                    $this->error("No email configured for tenant {$dbName}");
                    continue;
                }

                try {
                    Mail::to($email)->send(new LoanReminderMail($payment));

                    $this->info(
                        "Tenant {$dbName} - Payment ID {$payment->id} sent to {$email}"
                    );
                } catch (\Exception $e) {
                    $this->error(
                        "Mail failed for Payment ID {$payment->id}: " . $e->getMessage()
                    );
                }
            }
        }

        return Command::SUCCESS;
    }
}
