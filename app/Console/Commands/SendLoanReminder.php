<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoanReminderMail;
use App\Models\Tenant\BankAccount\Loan\Loan;

class SendLoanReminder extends Command
{
    protected $signature = 'loan:reminder';
    protected $description = 'Send loan reminder emails before 3 days of due date';

    public function handle()
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays(3);

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

            $loans = Loan::whereDate('next_due_date', $targetDate)
                ->where('status', 'active')
                ->get();

            foreach ($loans as $loan) {

                $email = config('loan.reminder_email') ?? config('mail.from.address');

                if (!$email) {
                    $this->error("No email configured for tenant {$dbName}");
                    continue;
                }

                Mail::to($email)->send(new LoanReminderMail($loan));

                $this->info("Tenant {$dbName} - Loan ID {$loan->id} sent to {$email}");
            }
        }

        return Command::SUCCESS;
    }
}
