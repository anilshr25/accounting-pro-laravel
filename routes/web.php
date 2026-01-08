<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Balance\BalanceController;
use App\Http\Controllers\Tenant\BankAccount\BankAccountController;
use App\Http\Controllers\Tenant\Cheque\ChequeController;
use App\Http\Controllers\Tenant\Credit\CreditController;
use App\Http\Controllers\Tenant\Customer\CustomerController;
use App\Http\Controllers\Tenant\Customer\Payment\CustomerPaymentController;
use App\Http\Controllers\Tenant\Daybook\DaybookController;
use App\Http\Controllers\Tenant\Invoice\InvoiceController;
use App\Http\Controllers\Tenant\Invoice\Item\InvoiceItemController;
use App\Http\Controllers\Tenant\PurchaseOrder\PurchaseOrderController;
use App\Http\Controllers\Tenant\PurchaseOrder\Item\PurchaseOrderItemController;
use App\Http\Controllers\Tenant\Supplier\SupplierController;
use App\Http\Controllers\Tenant\VendorPayment\VendorPaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('tenant')->group(function () {
    Route::get('balance', [BalanceController::class, 'index']);
    Route::post('balance', [BalanceController::class, 'store']);
    Route::put('balance/{id}', [BalanceController::class, 'update']);
    Route::delete('balance/{id}', [BalanceController::class, 'destroy']);

    Route::get('bank-account', [BankAccountController::class, 'index']);
    Route::post('bank-account', [BankAccountController::class, 'store']);
    Route::put('bank-account/{id}', [BankAccountController::class, 'update']);
    Route::delete('bank-account/{id}', [BankAccountController::class, 'destroy']);

    Route::get('cheque', [ChequeController::class, 'index']);
    Route::post('cheque', [ChequeController::class, 'store']);
    Route::put('cheque/{id}', [ChequeController::class, 'update']);
    Route::delete('cheque/{id}', [ChequeController::class, 'destroy']);

    Route::get('credit', [CreditController::class, 'index']);
    Route::post('credit', [CreditController::class, 'store']);
    Route::put('credit/{id}', [CreditController::class, 'update']);
    Route::delete('credit/{id}', [CreditController::class, 'destroy']);

    Route::get('customer', [CustomerController::class, 'index']);
    Route::post('customer', [CustomerController::class, 'store']);
    Route::put('customer/{id}', [CustomerController::class, 'update']);
    Route::delete('customer/{id}', [CustomerController::class, 'destroy']);

    Route::get('customer-payment', [CustomerPaymentController::class, 'index']);
    Route::post('customer-payment', [CustomerPaymentController::class, 'store']);
    Route::put('customer-payment/{id}', [CustomerPaymentController::class, 'update']);
    Route::delete('customer-payment/{id}', [CustomerPaymentController::class, 'destroy']);

    Route::get('daybook', [DaybookController::class, 'index']);
    Route::post('daybook', [DaybookController::class, 'store']);
    Route::put('daybook/{id}', [DaybookController::class, 'update']);
    Route::delete('daybook/{id}', [DaybookController::class, 'destroy']);

    Route::get('invoice', [InvoiceController::class, 'index']);
    Route::post('invoice', [InvoiceController::class, 'store']);
    Route::put('invoice/{id}', [InvoiceController::class, 'update']);
    Route::delete('invoice/{id}', [InvoiceController::class, 'destroy']);

    Route::get('invoice-item', [InvoiceItemController::class, 'index']);
    Route::post('invoice-item', [InvoiceItemController::class, 'store']);
    Route::put('invoice-item/{id}', [InvoiceItemController::class, 'update']);
    Route::delete('invoice-item/{id}', [InvoiceItemController::class, 'destroy']);

    Route::get('purchase-order', [PurchaseOrderController::class, 'index']);
    Route::post('purchase-order', [PurchaseOrderController::class, 'store']);
    Route::put('purchase-order/{id}', [PurchaseOrderController::class, 'update']);
    Route::delete('purchase-order/{id}', [PurchaseOrderController::class, 'destroy']);

    Route::get('purchase-order-item', [PurchaseOrderItemController::class, 'index']);
    Route::post('purchase-order-item', [PurchaseOrderItemController::class, 'store']);
    Route::put('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'update']);
    Route::delete('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'destroy']);

    Route::get('supplier', [SupplierController::class, 'index']);
    Route::post('supplier', [SupplierController::class, 'store']);
    Route::put('supplier/{id}', [SupplierController::class, 'update']);
    Route::delete('supplier/{id}', [SupplierController::class, 'destroy']);

    Route::get('vendor-payment', [VendorPaymentController::class, 'index']);
    Route::post('vendor-payment', [VendorPaymentController::class, 'store']);
    Route::put('vendor-payment/{id}', [VendorPaymentController::class, 'update']);
    Route::delete('vendor-payment/{id}', [VendorPaymentController::class, 'destroy']);
});
