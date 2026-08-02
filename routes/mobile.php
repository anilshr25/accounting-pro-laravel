<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\Auth\MFAController;
use App\Http\Controllers\Tenant\User\UserController;
use App\Http\Controllers\Tenant\Auth\LoginController;
use App\Http\Controllers\Tenant\Cheque\ChequeController;
use App\Http\Controllers\Tenant\Credit\CreditController;
use App\Http\Controllers\Tenant\Ledger\LedgerController;
use App\Http\Controllers\Tenant\Ledger\LedgerAdjustmentController;
use App\Http\Controllers\Tenant\Balance\BalanceController;
use App\Http\Controllers\Tenant\Daybook\DaybookController;
use App\Http\Controllers\Tenant\Invoice\InvoiceController;
use App\Http\Controllers\Tenant\Payment\PaymentController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\Tenant\Customer\CustomerController;
use App\Http\Controllers\Tenant\Supplier\SupplierController;
use App\Http\Controllers\Tenant\BankAccount\BankAccountController;
use App\Http\Controllers\Tenant\Invoice\Item\InvoiceItemController;
use App\Http\Controllers\Tenant\Purchase\Order\PurchaseOrderController;
use App\Http\Controllers\Tenant\Purchase\Order\Item\PurchaseOrderItemController;
use App\Http\Controllers\Tenant\Purchase\Return\PurchaseReturnController;
use App\Http\Controllers\Tenant\Purchase\Return\Item\PurchaseReturnItemController;
use App\Http\Controllers\Tenant\Invoice\Return\InvoiceReturnController;
use App\Http\Controllers\Tenant\Invoice\Return\Item\InvoiceReturnItemController;
use App\Http\Controllers\Tenant\Dashboard\DashboardController;
use App\Http\Controllers\Tenant\Product\ProductController;
use App\Http\Controllers\Tenant\Procurement\ProcurementController;
use App\Http\Controllers\Tenant\Procurement\Item\ProcurementItemController;
use App\Http\Controllers\Tenant\Employee\EmployeeController;
use App\Http\Controllers\Tenant\Attendance\AttendanceController;
use App\Http\Controllers\Tenant\Salary\SalaryController;
use App\Http\Controllers\Tenant\Scheme\SchemeController;
use App\Http\Controllers\Tenant\Scheme\Payment\SchemePaymentController;
use App\Http\Controllers\Tenant\BankAccount\Loan\LoanController;
use App\Http\Controllers\Tenant\BankAccount\Loan\Payment\LoanPaymentController;
use App\Http\Controllers\Tenant\Expenses\ExpensesController;
use App\Models\Tenant\Notification\Notification;
use App\Http\Controllers\Tenant\Kye\KyeController;
use App\Http\Controllers\Tenant\Kye\Address\KyeAddressController;
use App\Http\Controllers\Tenant\Kye\Education\KyeEducationController;
use App\Http\Controllers\Tenant\Kye\EmergencyContact\KyeEmergencyContactController;
use App\Http\Controllers\Tenant\Kye\Experience\KyeExperienceController;
use App\Http\Controllers\Tenant\Kye\Service\KyeServiceController;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Report\SalesReportController;
use App\Http\Controllers\Tenant\Report\LoanReportController;
use App\Http\Controllers\Tenant\Report\CreditReportController;
use App\Http\Controllers\Tenant\Report\PurchaseReportController;
use App\Http\Controllers\Tenant\Report\ChequeReportController;
use App\Http\Controllers\Tenant\Report\PaymentReportController;
use App\Http\Controllers\Tenant\Auth\AppLoginController;
use App\Http\Controllers\Tenant\Auth\AppMFAController;

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


Route::prefix('api/mobile')
    ->middleware([
        'tenant',
        'prevent_access_from_central_domains',
    ])
    ->group(function () {

        Route::post('login', [AppLoginController::class, 'login']);
        Route::post('check/verification-enabled', [AppMFAController::class, 'checkVerificationEnabled']);
    });

Route::prefix('api/mobile')
    ->middleware([
        'tenant',
        'prevent_access_from_central_domains',
        'auth:sanctum',
    ])
    ->group(function ($route) {

        $route->get('verify', [AppLoginController::class, 'doVerify']);
        $route->post('logout', [AppLoginController::class, 'logout']);

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
        $route->post('cheque/{id}/clear', [ChequeController::class, 'chequeClear']);
        $route->post('cheque/{id}/cancel', [ChequeController::class, 'chequeCancel']);
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
        $route->get('invoice/date-wise-summary', [InvoiceController::class, 'dateWiseSummary']);
        $route->post('invoice', [InvoiceController::class, 'store']);
        $route->get('invoice/{id}', [InvoiceController::class, 'show']);
        $route->put('invoice/{id}', [InvoiceController::class, 'update']);
        $route->delete('invoice/{id}', [InvoiceController::class, 'destroy']);

        $route->get('invoice-item', [InvoiceItemController::class, 'index']);
        $route->post('invoice-item', [InvoiceItemController::class, 'store']);
        $route->get('invoice-item/{id}', [InvoiceItemController::class, 'show']);
        $route->put('invoice-item/{id}', [InvoiceItemController::class, 'update']);
        $route->delete('invoice-item/{id}', [InvoiceItemController::class, 'destroy']);

        $route->post('ledger/adjustment', [LedgerAdjustmentController::class, 'adjust']);

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


        $route->get('purchase-return', [PurchaseReturnController::class, 'index']);
        $route->post('purchase-return', [PurchaseReturnController::class, 'store']);
        $route->get('purchase-return/{id}', [PurchaseReturnController::class, 'show']);
        $route->put('purchase-return/{id}', [PurchaseReturnController::class, 'update']);
        $route->delete('purchase-return/{id}', [PurchaseReturnController::class, 'destroy']);

        $route->get('purchase-return-item', [PurchaseReturnItemController::class, 'index']);
        $route->post('purchase-return-item', [PurchaseReturnItemController::class, 'store']);
        $route->get('purchase-return-item/{id}', [PurchaseReturnItemController::class, 'show']);
        $route->put('purchase-return-item/{id}', [PurchaseReturnItemController::class, 'update']);
        $route->delete('purchase-return-item/{id}', [PurchaseReturnItemController::class, 'destroy']);

        $route->get('invoice-return', [InvoiceReturnController::class, 'index']);
        $route->get('invoice-return/date-wise-summary', [InvoiceReturnController::class, 'dateWiseSummary']);
        $route->post('invoice-return', [InvoiceReturnController::class, 'store']);
        $route->get('invoice-return/{id}', [InvoiceReturnController::class, 'show']);
        $route->put('invoice-return/{id}', [InvoiceReturnController::class, 'update']);
        $route->delete('invoice-return/{id}', [InvoiceReturnController::class, 'destroy']);

        $route->get('invoice-return-item', [InvoiceReturnItemController::class, 'index']);
        $route->post('invoice-return-item', [InvoiceReturnItemController::class, 'store']);
        $route->get('invoice-return-item/{id}', [InvoiceReturnItemController::class, 'show']);
        $route->put('invoice-return-item/{id}', [InvoiceReturnItemController::class, 'update']);
        $route->delete('invoice-return-item/{id}', [InvoiceReturnItemController::class, 'destroy']);

        $route->get('product', [ProductController::class, 'index']);
        $route->post('product', [ProductController::class, 'store']);
        $route->get('product/{id}', [ProductController::class, 'show']);
        $route->put('product/{id}', [ProductController::class, 'update']);
        $route->delete('product/{id}', [ProductController::class, 'destroy']);

        $route->get('procurement', [ProcurementController::class, 'index']);
        $route->post('procurement', [ProcurementController::class, 'store']);
        $route->get('procurement/{id}', [ProcurementController::class, 'show']);
        $route->put('procurement/{id}', [ProcurementController::class, 'update']);
        $route->delete('procurement/{id}', [ProcurementController::class, 'destroy']);

        $route->get('procurement-item', [ProcurementItemController::class, 'index']);
        $route->post('procurement-item', [ProcurementItemController::class, 'store']);
        $route->get('procurement-item/{id}', [ProcurementItemController::class, 'show']);
        $route->put('procurement-item/{id}', [ProcurementItemController::class, 'update']);
        $route->delete('procurement-item/{id}', [ProcurementItemController::class, 'destroy']);

        $route->get('dashboard', [DashboardController::class, 'index']);
        $route->get('/dashboard/graph', [DashboardController::class, 'graph']);

        $route->get('ledger', [LedgerController::class, 'index']);
        $route->get('/ledger/export/pdf', [LedgerController::class, 'exportPdf']);

        $route->get('employee', [EmployeeController::class, 'index']);
        $route->post('employee', [EmployeeController::class, 'store']);
        $route->get('employee/{id}', [EmployeeController::class, 'show']);
        $route->put('employee/{id}', [EmployeeController::class, 'update']);
        $route->delete('employee/{id}', [EmployeeController::class, 'destroy']);

        $route->get('attendance', [AttendanceController::class, 'index']);
        $route->post('attendance', [AttendanceController::class, 'store']);
        $route->get('attendance/{id}', [AttendanceController::class, 'show']);
        $route->put('attendance/{id}', [AttendanceController::class, 'update']);
        $route->delete('attendance/{id}', [AttendanceController::class, 'destroy']);

        $route->get('salary', [SalaryController::class, 'index']);
        $route->post('salary', [SalaryController::class, 'store']);
        $route->get('salary/{id}', [SalaryController::class, 'show']);
        $route->put('salary/{id}', [SalaryController::class, 'update']);
        $route->delete('salary/{id}', [SalaryController::class, 'destroy']);

        $route->get('scheme', [SchemeController::class, 'index']);
        $route->post('scheme', [SchemeController::class, 'store']);
        $route->get('scheme/{id}', [SchemeController::class, 'show']);
        $route->put('scheme/{id}', [SchemeController::class, 'update']);
        $route->delete('scheme/{id}', [SchemeController::class, 'destroy']);

        $route->get('scheme-payment', [SchemePaymentController::class, 'index']);
        $route->post('scheme-payment', [SchemePaymentController::class, 'store']);
        $route->get('scheme-payment/{id}', [SchemePaymentController::class, 'show']);
        $route->put('scheme-payment/{id}', [SchemePaymentController::class, 'update']);
        $route->delete('scheme-payment/{id}', [SchemePaymentController::class, 'destroy']);

        $route->get('loan', [LoanController::class, 'index']);
        $route->post('loan', [LoanController::class, 'store']);
        $route->get('loan/{id}', [LoanController::class, 'show']);
        $route->put('loan/{id}', [LoanController::class, 'update']);
        $route->delete('loan/{id}', [LoanController::class, 'destroy']);

        $route->get('loan-payment', [LoanPaymentController::class, 'index']);
        $route->post('loan-payment', [LoanPaymentController::class, 'store']);
        $route->get('loan-payment/{id}', [LoanPaymentController::class, 'show']);
        $route->put('loan-payment/{id}', [LoanPaymentController::class, 'update']);
        $route->delete('loan-payment/{id}', [LoanPaymentController::class, 'destroy']);

        $route->get('expenses', [ExpensesController::class, 'index']);
        $route->post('expenses', [ExpensesController::class, 'store']);
        $route->get('expenses/{id}', [ExpensesController::class, 'show']);
        $route->put('expenses/{id}', [ExpensesController::class, 'update']);
        $route->delete('expenses/{id}', [ExpensesController::class, 'destroy']);

        $route->get('kye', [KyeController::class, 'index']);
        $route->post('kye', [KyeController::class, 'store']);
        $route->get('kye/{id}', [KyeController::class, 'show']);
        $route->put('kye/{id}', [KyeController::class, 'update']);
        $route->delete('kye/{id}', [KyeController::class, 'destroy']);

        $route->get('kye-address', [KyeAddressController::class, 'index']);
        $route->post('kye-address', [KyeAddressController::class, 'store']);
        $route->get('kye-address/{id}', [KyeAddressController::class, 'show']);
        $route->put('kye-address/{id}', [KyeAddressController::class, 'update']);
        $route->delete('kye-address/{id}', [KyeAddressController::class, 'destroy']);

        $route->get('kye-education', [KyeEducationController::class, 'index']);
        $route->post('kye-education', [KyeEducationController::class, 'store']);
        $route->get('kye-education/{id}', [KyeEducationController::class, 'show']);
        $route->put('kye-education/{id}', [KyeEducationController::class, 'update']);
        $route->delete('kye-education/{id}', [KyeEducationController::class, 'destroy']);

        $route->get('kye-emergency-contact', [KyeEmergencyContactController::class, 'index']);
        $route->post('kye-emergency-contact', [KyeEmergencyContactController::class, 'store']);
        $route->get('kye-emergency-contact/{id}', [KyeEmergencyContactController::class, 'show']);
        $route->put('kye-emergency-contact/{id}', [KyeEmergencyContactController::class, 'update']);
        $route->delete('kye-emergency-contact/{id}', [KyeEmergencyContactController::class, 'destroy']);

        $route->get('kye-experience', [KyeExperienceController::class, 'index']);
        $route->post('kye-experience', [KyeExperienceController::class, 'store']);
        $route->get('kye-experience/{id}', [KyeExperienceController::class, 'show']);
        $route->put('kye-experience/{id}', [KyeExperienceController::class, 'update']);
        $route->delete('kye-experience/{id}', [KyeExperienceController::class, 'destroy']);

        $route->get('kye-service', [KyeServiceController::class, 'index']);
        $route->post('kye-service', [KyeServiceController::class, 'store']);
        $route->get('kye-service/{id}', [KyeServiceController::class, 'show']);
        $route->put('kye-service/{id}', [KyeServiceController::class, 'update']);
        $route->delete('kye-service/{id}', [KyeServiceController::class, 'destroy']);

        $route->get('payroll', [PayrollController::class, 'index']);
        $route->get('sales-report', [SalesReportController::class, 'index']);
        $route->get('bank-loan-report', [LoanReportController::class, 'index']);
        $route->get('credit-report', [CreditReportController::class, 'index']);
        $route->get('purchase-report', [PurchaseReportController::class, 'index']);
        $route->get('cheque-report', [ChequeReportController::class, 'index']);
        $route->get('payment-report', [PaymentReportController::class, 'index']);

        $route->get('/notifications', function () {
            return Notification::latest()->get();
        });
    });
