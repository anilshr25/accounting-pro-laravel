<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Auth\MFAController;
use App\Http\Controllers\Tenant\User\UserController;
use App\Http\Controllers\Tenant\Auth\LoginController;
use App\Http\Controllers\Tenant\Cheque\ChequeController;
use App\Http\Controllers\Tenant\Credit\CreditController;
use App\Http\Controllers\Tenant\Ledger\LedgerController;
use App\Http\Controllers\Tenant\Balance\BalanceController;
use App\Http\Controllers\Tenant\Daybook\DaybookController;
use App\Http\Controllers\Tenant\Invoice\InvoiceController;
use App\Http\Controllers\Tenant\Payment\PaymentController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\Tenant\Customer\CustomerController;
use App\Http\Controllers\Tenant\Supplier\SupplierController;
use App\Http\Controllers\Tenant\BankAccount\BankAccountController;
use App\Http\Controllers\Tenant\Invoice\Item\InvoiceItemController;
use App\Http\Controllers\Tenant\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\Tenant\PurchaseOrder\Item\PurchaseOrderItemController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// SPA Sanctum CSRF cookie endpoint for tenant domains
Route::middleware(['tenant', 'prevent_access_from_central_domains', 'web'])
    ->get('/api/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])
    ->name('tenant.sanctum.csrf-cookie');

Route::group(['prefix' => 'api', 'middleware' => ['tenant', 'prevent_access_from_central_domains', 'stateful', 'web']], function ($route) {

    $route->post('check/verification-enabled', [MFAController::class, 'checkVerificationEnabled']);

    $route->post('login', [LoginController::class, 'login']);

    $route->post('verify/mfa-verification-code', [MFAController::class, 'verifyMfaVerificationCode']);

    $route->post('verify/email-verification-code', [MFAController::class, 'verifyEmailVerificationCode']);

    $route->post('request/verification-code', [MFAController::class, 'requestEmailVerificationCode']);

});

// Tenant API routes
Route::prefix('api')->middleware(['tenant', 'prevent_access_from_central_domains', 'stateful', 'web', 'user'])->group(function ($route) {

    $route->post('logout', [LoginController::class, 'logout'])->name('logout');
    $route->get('do-verify', [LoginController::class, 'doVerify']);

    $route->get('balance', [BalanceController::class, 'index']);
    $route->post('balance', [BalanceController::class, 'store']);
    $route->get('balance/{id}', [BalanceController::class, 'show']);
    $route->put('balance/{id}', [BalanceController::class, 'update']);
    $route->delete('balance/{id}', [BalanceController::class, 'destroy']);

    $route->get('bank-account', [BankAccountController::class, 'index']);
    $route->post('bank-account', [BankAccountController::class, 'store']);
    $route->get('bank-account/{id}', [BankAccountController::class, 'show']);
    $route->put('bank-account/{id}', [BankAccountController::class, 'update']);
    $route->delete('bank-account/{id}', [BankAccountController::class, 'destroy']);

    $route->get('cheque', [ChequeController::class, 'index']);
    $route->post('cheque', [ChequeController::class, 'store']);
    $route->get('cheque/{id}', [ChequeController::class, 'show']);
    $route->put('cheque/{id}', [ChequeController::class, 'update']);
    $route->delete('cheque/{id}', [ChequeController::class, 'destroy']);

    $route->get('credit', [CreditController::class, 'index']);
    $route->post('credit', [CreditController::class, 'store']);
    $route->get('credit/{id}', [CreditController::class, 'show']);
    $route->put('credit/{id}', [CreditController::class, 'update']);
    $route->delete('credit/{id}', [CreditController::class, 'destroy']);

    $route->get('customer', [CustomerController::class, 'index']);
    $route->get('customer/get/search', [CustomerController::class, 'search'])->name('tenant.customer.search');
    $route->post('customer', [CustomerController::class, 'store']);
    $route->get('customer/{id}', [CustomerController::class, 'show']);
    $route->put('customer/{id}', [CustomerController::class, 'update']);
    $route->delete('customer/{id}', [CustomerController::class, 'destroy']);


    $route->get('daybook', [DaybookController::class, 'index']);
    $route->post('daybook', [DaybookController::class, 'store']);
    $route->get('daybook/{id}', [DaybookController::class, 'show']);
    $route->put('daybook/{id}', [DaybookController::class, 'update']);
    $route->delete('daybook/{id}', [DaybookController::class, 'destroy']);

    $route->get('invoice', [InvoiceController::class, 'index']);
    $route->post('invoice', [InvoiceController::class, 'store']);
    $route->get('invoice/{id}', [InvoiceController::class, 'show']);
    $route->put('invoice/{id}', [InvoiceController::class, 'update']);
    $route->delete('invoice/{id}', [InvoiceController::class, 'destroy']);

    $route->get('invoice-item', [InvoiceItemController::class, 'index']);
    $route->post('invoice-item', [InvoiceItemController::class, 'store']);
    $route->get('invoice-item/{id}', [InvoiceItemController::class, 'show']);
    $route->put('invoice-item/{id}', [InvoiceItemController::class, 'update']);
    $route->delete('invoice-item/{id}', [InvoiceItemController::class, 'destroy']);

    $route->get('payment', [PaymentController::class, 'index']);
    $route->post('payment', [PaymentController::class, 'store']);
    $route->get('payment/{id}', [PaymentController::class, 'show']);
    $route->put('payment/{id}', [PaymentController::class, 'update']);
    $route->delete('payment/{id}', [PaymentController::class, 'destroy']);

    $route->get('purchase-order', [PurchaseOrderController::class, 'index']);
    $route->post('purchase-order', [PurchaseOrderController::class, 'store']);
    $route->get('purchase-order/{id}', [PurchaseOrderController::class, 'show']);
    $route->put('purchase-order/{id}', [PurchaseOrderController::class, 'update']);
    $route->delete('purchase-order/{id}', [PurchaseOrderController::class, 'destroy']);

    $route->get('purchase-order-item', [PurchaseOrderItemController::class, 'index']);
    $route->post('purchase-order-item', [PurchaseOrderItemController::class, 'store']);
    $route->get('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'show']);
    $route->put('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'update']);
    $route->delete('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'destroy']);

    $route->get('supplier', [SupplierController::class, 'index']);
    $route->get('supplier/get/search', [SupplierController::class, 'search'])->name('tenant.supplier.search');
    $route->post('supplier', [SupplierController::class, 'store']);
    $route->get('supplier/{id}', [SupplierController::class, 'show']);
    $route->put('supplier/{id}', [SupplierController::class, 'update']);
    $route->delete('supplier/{id}', [SupplierController::class, 'destroy']);


    $route->get('user', [UserController::class, 'index']);
    $route->post('user', [UserController::class, 'store']);
    $route->get('user/{id}', [UserController::class, 'show']);
    $route->put('user/{id}', [UserController::class, 'update']);
    $route->delete('user/{id}', [UserController::class, 'destroy']);


    $route->get('ledger', [LedgerController::class, 'index']);
});
