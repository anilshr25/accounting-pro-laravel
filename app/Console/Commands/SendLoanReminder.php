<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tenant\BankAccount\Loan\Payment\LoanPayment;
use App\Models\Tenant\Notification\Notification;

class SendLoanReminder extends Command
{
    protected $signature = 'loan:reminder';
    protected $description = 'Send loan payment reminders before due date';

    public function handle()
    {
        $targetDate = Carbon::today()->addDays(3)->toDateString();

        $tenants = DB::table('tenants')->get();

        foreach ($tenants as $tenant) {

            $data = json_decode($tenant->data, true);
            $dbName = $data['tenancy_db_name'] ?? null;

            if (!$dbName) {
                $this->error("No database for tenant {$tenant->id}");
                continue;
            }

            config(['database.connections.mysql.database' => $dbName]);
            DB::purge('mysql');
            DB::reconnect('mysql');

            $payments = LoanPayment::whereDate('due_date', $targetDate)->get();

            foreach ($payments as $payment) {

                Notification::create([
                    'title' => 'Loan Payment Reminder',
                    'message' => "Your loan payment is due on {$payment->due_date}",
                    'loan_id' => $payment->loan_id,
                    'payment_id' => $payment->id,
                    'is_read' => false,
                ]);

                $this->info("Notification created for payment {$payment->id}");
            }
        }

        return Command::SUCCESS;
    }
};
