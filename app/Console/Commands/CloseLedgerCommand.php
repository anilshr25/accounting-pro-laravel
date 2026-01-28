<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Ledger\Ledger;
use App\Models\Tenant\Supplier\Supplier;

class CloseLedgerCommand extends Command
{
    protected $signature = 'ledger:close {--date=}';

    protected $description = 'Close supplier ledger balances for a given date and roll opening balances';

    public function handle(): int
    {
        $endDate = $this->resolveEndDate();

        if (!$endDate) {
            $this->error('End date not set. Provide --date=YYYY-MM-DD or set closing_date in settings.');
            return self::FAILURE;
        }

        $this->info("Closing ledger for date: {$endDate}");

        $suppliers = Supplier::query()->get();
        foreach ($suppliers as $supplier) {
            $lastBalance = Ledger::query()
                ->where('party_type', 'supplier')
                ->where('party_id', $supplier->id)
                ->whereDate('date', '<=', $endDate)
                ->orderBy('date', 'ASC')
                ->orderBy('id', 'ASC')
                ->value('balance');

            $closingBalance = $lastBalance ?? $supplier->opening_balance ?? 0;

            $supplier->update([
                'closing_balance' => $closingBalance,
                'opening_balance' => $closingBalance,
            ]);
        }

        $this->info('✓ Supplier balances closed and opening balances updated.');
        return self::SUCCESS;
    }

    protected function resolveEndDate(): ?string
    {
        if ($this->option('date')) {
            return $this->option('date');
        }

        if (function_exists('getSetting')) {
            $setting = getSetting();
            return $setting?->ledger_closing_date  ?? null;
        }

        return null;
    }
}
