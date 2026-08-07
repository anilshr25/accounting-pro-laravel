<?php

use App\Http\Controllers\Tenant\Ledger\LedgerController;
use App\Http\Controllers\Tenant\Storage\TenantFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));
Route::get('private-files/show', TenantFileController::class)
    ->middleware('signed')
    ->name('central.storage.show');

Route::middleware(['web'])->group(function ($route): void {
    Route::get('ledger/pdf/{party}/{party_type}', [LedgerController::class, 'downloadPdf'])
        ->name('ledger.pdf')
        ->middleware(['web', 'tenant', 'prevent_access_from_central_domains', 'user']);
});
