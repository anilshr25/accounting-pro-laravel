<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Ledger\LedgerController;

Route::get('/', fn() => view('welcome'));

Route::middleware(['web'])->group(function ($route): void {
    include base_path('routes/tenant.php');
    Route::get('ledger/pdf/{party}/{party_type}', [LedgerController::class, 'downloadPdf'])
        ->name('ledger.pdf')
        ->middleware(['web', 'tenant']);
});
