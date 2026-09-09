<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Attendance\AttendanceController;
use App\Http\Controllers\Tenant\Auth\LoginController;
use App\Http\Controllers\Tenant\Auth\MFAController;
use App\Http\Controllers\Tenant\Balance\BalanceController;
use App\Http\Controllers\Tenant\BankAccount\BankAccountController;
use App\Http\Controllers\Tenant\BankAccount\Loan\LoanController;
use App\Http\Controllers\Tenant\BankAccount\Loan\Payment\LoanPaymentController;
use App\Http\Controllers\Tenant\Cheque\ChequeController;
use App\Http\Controllers\Tenant\Credit\CreditController;
use App\Http\Controllers\Tenant\Customer\CustomerController;
use App\Http\Controllers\Tenant\Dashboard\DashboardController;
use App\Http\Controllers\Tenant\Daybook\DaybookController;
use App\Http\Controllers\Tenant\Employee\EmployeeController;
use App\Http\Controllers\Tenant\Expenses\ExpensesController;
use App\Http\Controllers\Tenant\Invoice\InvoiceController;
use App\Http\Controllers\Tenant\Invoice\Item\InvoiceItemController;
use App\Http\Controllers\Tenant\Invoice\Return\InvoiceReturnController;
use App\Http\Controllers\Tenant\Invoice\Return\Item\InvoiceReturnItemController;
use App\Http\Controllers\Tenant\Kye\Address\KyeAddressController;
use App\Http\Controllers\Tenant\Kye\Education\KyeEducationController;
use App\Http\Controllers\Tenant\Kye\EmergencyContact\KyeEmergencyContactController;
use App\Http\Controllers\Tenant\Kye\Experience\KyeExperienceController;
use App\Http\Controllers\Tenant\Kye\KyeController;
use App\Http\Controllers\Tenant\Kye\Service\KyeServiceController;
use App\Http\Controllers\Tenant\Ledger\LedgerAdjustmentController;
use App\Http\Controllers\Tenant\Ledger\LedgerController;
use App\Http\Controllers\Tenant\Payment\PaymentController;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Http\Controllers\Tenant\Procurement\Item\ProcurementItemController;
use App\Http\Controllers\Tenant\Procurement\ProcurementController;
use App\Http\Controllers\Tenant\Product\ProductController;
use App\Http\Controllers\Tenant\Purchase\Order\Item\PurchaseOrderItemController;
use App\Http\Controllers\Tenant\Purchase\Order\PurchaseOrderController;
use App\Http\Controllers\Tenant\Purchase\Return\Item\PurchaseReturnItemController;
use App\Http\Controllers\Tenant\Purchase\Return\PurchaseReturnController;
use App\Http\Controllers\Tenant\Report\ChequeReportController;
use App\Http\Controllers\Tenant\Report\CreditReportController;
use App\Http\Controllers\Tenant\Report\LoanReportController;
use App\Http\Controllers\Tenant\Report\PaymentReportController;
use App\Http\Controllers\Tenant\Report\PurchaseReportController;
use App\Http\Controllers\Tenant\Report\SalesReportController;
use App\Http\Controllers\Tenant\Salary\SalaryController;
use App\Http\Controllers\Tenant\Scheme\Payment\SchemePaymentController;
use App\Http\Controllers\Tenant\Scheme\SchemeController;
use App\Http\Controllers\Tenant\SiteSetting\SiteSettingController;
use App\Http\Controllers\Tenant\Storage\TenantFileController;
use App\Http\Controllers\Tenant\Supplier\SupplierController;
use App\Http\Controllers\Tenant\User\UserController;
use App\Models\Tenant\Notification\Notification;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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

// Route::group(['prefix' => 'api', 'middleware' => ['tenant', 'prevent_access_from_central_domains', 'stateful', 'web']], function ($route) {

//     $route->post('check/verification-enabled', [MFAController::class, 'checkVerificationEnabled']);

//     $route->post('login', [LoginController::class, 'login']);

//     $route->post('verify/mfa-verification-code', [MFAController::class, 'verifyMfaVerificationCode']);

//     $route->post('verify/email-verification-code', [MFAController::class, 'verifyEmailVerificationCode']);

//     $route->post('request/verification-code', [MFAController::class, 'requestEmailVerificationCode']);
// });

// Tenant API routes
Route::prefix('api')->middleware(['web', 'central.auth', 'tenant.session'])->group(function ($route) {

    $route->middleware('tenant.owner')->group(function ($route): void {
        $route->get('site-setting', [SiteSettingController::class, 'show']);
        $route->put('site-setting', [SiteSettingController::class, 'update']);
    });
    $route->get('tenant-files/show', TenantFileController::class)
        ->middleware('signed')
        ->name('tenant.storage.show');

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

    $route->get('cheque', [ChequeController::class, 'index'])
        ->middleware('permission.type:cheque,view');
    $route->post('cheque', [ChequeController::class, 'store'])
        ->middleware('permission.type:cheque,create');
    $route->get('cheque/{id}', [ChequeController::class, 'show'])
        ->middleware('permission.type:cheque,view');
    $route->post('cheque/{id}/clear', [ChequeController::class, 'chequeClear'])
        ->middleware('permission.type:cheque,clear');
    $route->post('cheque/{id}/cancel', [ChequeController::class, 'chequeCancel'])
        ->middleware('permission.type:cheque,cancel');
    $route->put('cheque/{id}', [ChequeController::class, 'update'])
        ->middleware('permission.type:cheque,update');
    $route->delete('cheque/{id}', [ChequeController::class, 'destroy'])
        ->middleware('permission.type:cheque,delete');

    $route->get('credit', [CreditController::class, 'index'])
        ->middleware('permission.type:credit,view');
    $route->post('credit', [CreditController::class, 'store'])
        ->middleware('permission.type:credit,create');
    $route->get('credit/{id}', [CreditController::class, 'show'])
        ->middleware('permission.typecredit,view');
    $route->put('credit/{id}', [CreditController::class, 'update'])
        ->middleware('permission.type:credit,update');
    $route->delete('credit/{id}', [CreditController::class, 'destroy'])
        ->middleware('permission.type:credit,delete');

    $route->get('customer', [CustomerController::class, 'index'])
        ->middleware('permission.type:customer,view');
    $route->get('customer/get/search', [CustomerController::class, 'search'])->name('tenant.customer.search')
        ->middleware('permission.type:customer,search');
    $route->post('customer', [CustomerController::class, 'store'])
        ->middleware('permission.type:customer,create');
    $route->get('customer/{id}', [CustomerController::class, 'show'])
        ->middleware('permission.type:customer,view');
    $route->put('customer/{id}', [CustomerController::class, 'update'])
        ->middleware('permission.type:customer,update');
    $route->delete('customer/{id}', [CustomerController::class, 'destroy'])
        ->middleware('permission.type:customer,delete');

    $route->get('daybook', [DaybookController::class, 'index']);
    $route->post('daybook', [DaybookController::class, 'store']);
    $route->get('daybook/{id}', [DaybookController::class, 'show']);
    $route->put('daybook/{id}', [DaybookController::class, 'update']);
    $route->delete('daybook/{id}', [DaybookController::class, 'destroy']);

    $route->get('invoice', [InvoiceController::class, 'index'])
        ->middleware('permission.type:sales,view');
    $route->get('invoice/date-wise-summary', [InvoiceController::class, 'dateWiseSummary'])
        ->middleware('permission.type:sales,view');
    $route->post('invoice', [InvoiceController::class, 'store'])
        ->middleware('permission.type:sales,create');
    $route->get('invoice/{id}', [InvoiceController::class, 'show'])
        ->middleware('permission.type:sales,view');
    $route->put('invoice/{id}', [InvoiceController::class, 'update'])
        ->middleware('permission.type:sales,update');
    $route->delete('invoice/{id}', [InvoiceController::class, 'destroy'])
        ->middleware('permission.type:sales,delete');

    $route->get('invoice-item', [InvoiceItemController::class, 'index']);
    $route->post('invoice-item', [InvoiceItemController::class, 'store']);
    $route->get('invoice-item/{id}', [InvoiceItemController::class, 'show']);
    $route->put('invoice-item/{id}', [InvoiceItemController::class, 'update']);
    $route->delete('invoice-item/{id}', [InvoiceItemController::class, 'destroy']);

    $route->post('ledger/adjustment', [LedgerAdjustmentController::class, 'adjust']);

    $route->get('payment', [PaymentController::class, 'index'])
        ->middleware('permission.type:payment,view');
    $route->post('payment', [PaymentController::class, 'store'])
        ->middleware('permission.type:payment,create');
    $route->get('payment/{id}', [PaymentController::class, 'show'])
        ->middleware('permission.type:payment,view');
    $route->put('payment/{id}', [PaymentController::class, 'update'])
        ->middleware('permission.type:payment,update');
    $route->delete('payment/{id}', [PaymentController::class, 'destroy'])
        ->middleware('permission.type:payment,delete');

    $route->get('purchase-order', [PurchaseOrderController::class, 'index'])
        ->middleware('permission.type:purchase,view');
    $route->post('purchase-order', [PurchaseOrderController::class, 'store'])
        ->middleware('permission.type:purchase,create');;
    $route->get('purchase-order/{id}', [PurchaseOrderController::class, 'show'])
        ->middleware('permission.type:purchase,view');;
    $route->put('purchase-order/{id}', [PurchaseOrderController::class, 'update'])
        ->middleware('permission.type:purchase,update');;
    $route->delete('purchase-order/{id}', [PurchaseOrderController::class, 'destroy'])
        ->middleware('permission.type:purchase,delete');

    $route->get('purchase-order-item', [PurchaseOrderItemController::class, 'index']);
    $route->post('purchase-order-item', [PurchaseOrderItemController::class, 'store']);
    $route->get('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'show']);
    $route->put('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'update']);
    $route->delete('purchase-order-item/{id}', [PurchaseOrderItemController::class, 'destroy']);

    $route->get('supplier', [SupplierController::class, 'index'])
        ->middleware('permission.type:supplier,view');
    $route->get('supplier/get/search', [SupplierController::class, 'search'])->name('tenant.supplier.search')
        ->middleware('permission.type:supplier,search');
    $route->post('supplier', [SupplierController::class, 'store'])
        ->middleware('permission.type:supplier,create');
    $route->get('supplier/{id}', [SupplierController::class, 'show'])
        ->middleware('permission.type:supplier,view');
    $route->put('supplier/{id}', [SupplierController::class, 'update'])
        ->middleware('permission.type:supplier,update');
    $route->delete('supplier/{id}', [SupplierController::class, 'destroy'])
        ->middleware('permission.type:supplier,delete');
    $route->post('supplier/export', [SupplierController::class, 'exportPdf'])
        ->middleware('permission.type:supplier,export');
    $route->get('supplier/export/pdf', [SupplierController::class, 'downloadExportPdf'])->name('supplier.export.pdf')
        ->middleware('permission.type:supplier,export');

    $route->get('user', [UserController::class, 'index'])
        ->middleware('permission.type:user,view');
    $route->post('user', [UserController::class, 'store'])
        ->middleware('permission.type:user,create');
    $route->get('user/{id}', [UserController::class, 'show'])
        ->middleware('permission.type:user,view');
    $route->put('user/{id}', [UserController::class, 'update'])
        ->middleware('permission.type:user,update');
    $route->delete('user/{id}', [UserController::class, 'destroy'])
        ->middleware('permission.type:user,delete');

    $route->get('purchase-return', [PurchaseReturnController::class, 'index'])
        ->middleware('permission.type:purchase-return,view');
    $route->post('purchase-return', [PurchaseReturnController::class, 'store'])
        ->middleware('permission.type:purchase-return,create');
    $route->get('purchase-return/{id}', [PurchaseReturnController::class, 'show'])
        ->middleware('permission.type:purchase-return,view');
    $route->put('purchase-return/{id}', [PurchaseReturnController::class, 'update'])
        ->middleware('permission.type:purchase-return,update');
    $route->delete('purchase-return/{id}', [PurchaseReturnController::class, 'destroy'])
        ->middleware('permission.type:purchase-return,delete');

    $route->get('purchase-return-item', [PurchaseReturnItemController::class, 'index']);
    $route->post('purchase-return-item', [PurchaseReturnItemController::class, 'store']);
    $route->get('purchase-return-item/{id}', [PurchaseReturnItemController::class, 'show']);
    $route->put('purchase-return-item/{id}', [PurchaseReturnItemController::class, 'update']);
    $route->delete('purchase-return-item/{id}', [PurchaseReturnItemController::class, 'destroy']);

    $route->get('invoice-return', [InvoiceReturnController::class, 'index'])
        ->middleware('permission.type:sales-return,view');
    $route->get('invoice-return/date-wise-summary', [InvoiceReturnController::class, 'dateWiseSummary'])
        ->middleware('permission.type:sales-return,view');
    $route->post('invoice-return', [InvoiceReturnController::class, 'store'])
        ->middleware('permission.type:sales-return,create');
    $route->get('invoice-return/{id}', [InvoiceReturnController::class, 'show'])
        ->middleware('permission.type:sales-return,view');
    $route->put('invoice-return/{id}', [InvoiceReturnController::class, 'update'])
        ->middleware('permission.type:sales-return,update');
    $route->delete('invoice-return/{id}', [InvoiceReturnController::class, 'destroy'])
        ->middleware('permission.type:sales-return,delete');

    $route->get('invoice-return-item', [InvoiceReturnItemController::class, 'index']);
    $route->post('invoice-return-item', [InvoiceReturnItemController::class, 'store']);
    $route->get('invoice-return-item/{id}', [InvoiceReturnItemController::class, 'show']);
    $route->put('invoice-return-item/{id}', [InvoiceReturnItemController::class, 'update']);
    $route->delete('invoice-return-item/{id}', [InvoiceReturnItemController::class, 'destroy']);

    $route->get('product', [ProductController::class, 'index'])
        ->middleware('permission.type:product,view');
    $route->post('product', [ProductController::class, 'store'])
        ->middleware('permission.type:product,create');
    $route->get('product/{id}', [ProductController::class, 'show'])
        ->middleware('permission.type:product,view');
    $route->put('product/{id}', [ProductController::class, 'update'])
        ->middleware('permission.type:product,update');
    $route->delete('product/{id}', [ProductController::class, 'destroy'])
        ->middleware('permission.type:product,delete');

    $route->get('procurement', [ProcurementController::class, 'index'])
        ->middleware('permission.type:procurement,view');
    $route->post('procurement', [ProcurementController::class, 'store'])
        ->middleware('permission.type:procurement,create');
    $route->get('procurement/{id}', [ProcurementController::class, 'show'])
        ->middleware('permission.type:procurement,view');
    $route->put('procurement/{id}', [ProcurementController::class, 'update'])
        ->middleware('permission.type:procurement,update');
    $route->delete('procurement/{id}', [ProcurementController::class, 'destroy'])
        ->middleware('permission.type:procurement,delete');

    $route->get('procurement-item', [ProcurementItemController::class, 'index']);
    $route->post('procurement-item', [ProcurementItemController::class, 'store']);
    $route->get('procurement-item/{id}', [ProcurementItemController::class, 'show']);
    $route->put('procurement-item/{id}', [ProcurementItemController::class, 'update']);
    $route->delete('procurement-item/{id}', [ProcurementItemController::class, 'destroy']);

    $route->get('dashboard', [DashboardController::class, 'index']);
    $route->get('/dashboard/graph', [DashboardController::class, 'graph']);

    $route->get('ledger', [LedgerController::class, 'index']);
    $route->get('/ledger/export/pdf', [LedgerController::class, 'exportPdf']);

    $route->get('employee', [EmployeeController::class, 'index'])
        ->middleware('permission.type:employee,view');
    $route->post('employee', [EmployeeController::class, 'store'])
        ->middleware('permission.type:employee,create');
    $route->get('employee/{id}', [EmployeeController::class, 'show'])
        ->middleware('permission.type:employee,view');
    $route->put('employee/{id}', [EmployeeController::class, 'update'])
        ->middleware('permission.type:employee,update');
    $route->delete('employee/{id}', [EmployeeController::class, 'destroy'])
        ->middleware('permission.type:employee,delete');

    $route->get('attendance', [AttendanceController::class, 'index']);
    $route->post('attendance', [AttendanceController::class, 'store']);
    $route->get('attendance/{id}', [AttendanceController::class, 'show']);
    $route->put('attendance/{id}', [AttendanceController::class, 'update']);
    $route->delete('attendance/{id}', [AttendanceController::class, 'destroy']);

    $route->get('salary', [SalaryController::class, 'index'])
        ->middleware('permission.type:salary,view');
    $route->post('salary', [SalaryController::class, 'store'])
        ->middleware('permission.type:salary,create');
    $route->get('salary/{id}', [SalaryController::class, 'show'])
        ->middleware('permission.type:salary,view');
    $route->put('salary/{id}', [SalaryController::class, 'update'])
        ->middleware('permission.type:salary,update');
    $route->delete('salary/{id}', [SalaryController::class, 'destroy'])
        ->middleware('permission.type:salary,delete');

    $route->get('scheme', [SchemeController::class, 'index'])
        ->middleware('permission.type:scheme,view');
    $route->post('scheme', [SchemeController::class, 'store'])
        ->middleware('permission.type:scheme,create');
    $route->get('scheme/{id}', [SchemeController::class, 'show'])
        ->middleware('permission.type:scheme,view');
    $route->put('scheme/{id}', [SchemeController::class, 'update'])
        ->middleware('permission.type:scheme,update');
    $route->delete('scheme/{id}', [SchemeController::class, 'destroy'])
        ->middleware('permission.type:scheme,delete');

    $route->get('scheme-payment', [SchemePaymentController::class, 'index'])
        ->middleware('permission.type:scheme-payment,view');
    $route->post('scheme-payment', [SchemePaymentController::class, 'store'])
        ->middleware('permission.type:scheme-payment,create');
    $route->get('scheme-payment/{id}', [SchemePaymentController::class, 'show'])
        ->middleware('permission.type:scheme-payment,view');
    $route->put('scheme-payment/{id}', [SchemePaymentController::class, 'update'])
        ->middleware('permission.type:scheme-payment,update');
    $route->delete('scheme-payment/{id}', [SchemePaymentController::class, 'destroy'])
        ->middleware('permission.type:scheme-payment,delete');

    $route->get('loan', [LoanController::class, 'index'])
        ->middleware('permission.type:loan,view');
    $route->post('loan', [LoanController::class, 'store'])
        ->middleware('permission.type:loan,create');
    $route->get('loan/{id}', [LoanController::class, 'show'])
        ->middleware('permission.type:loan,view');
    $route->put('loan/{id}', [LoanController::class, 'update'])
        ->middleware('permission.type:loan,update');
    $route->delete('loan/{id}', [LoanController::class, 'destroy'])
        ->middleware('permission.type:loan,delete');

    $route->get('loan-payment', [LoanPaymentController::class, 'index'])
        ->middleware('permission.type:loan-payment,view');
    $route->post('loan-payment', [LoanPaymentController::class, 'store'])
        ->middleware('permission.type:loan-payment,create');
    $route->get('loan-payment/{id}', [LoanPaymentController::class, 'show'])
        ->middleware('permission.type:loan-payment,view');
    $route->put('loan-payment/{id}', [LoanPaymentController::class, 'update'])
        ->middleware('permission.type:loan-payment,update');
    $route->delete('loan-payment/{id}', [LoanPaymentController::class, 'destroy'])
        ->middleware('permission.type:loan-payment,delete');

    $route->get('expenses', [ExpensesController::class, 'index'])
        ->middleware('permission.type:expenses,view');
    $route->post('expenses', [ExpensesController::class, 'store'])
        ->middleware('permission.type:expenses,create');
    $route->get('expenses/{id}', [ExpensesController::class, 'show'])
        ->middleware('permission.type:expenses,view');
    $route->put('expenses/{id}', [ExpensesController::class, 'update'])
        ->middleware('permission.type:expenses,update');
    $route->delete('expenses/{id}', [ExpensesController::class, 'destroy'])
        ->middleware('permission.type:expenses,delete');

    $route->get('kye', [KyeController::class, 'index'])
        ->middleware('permission.type:kye,view');
    $route->post('kye', [KyeController::class, 'store'])
        ->middleware('permission.type:kye,create');
    $route->get('kye/{id}', [KyeController::class, 'show'])
        ->middleware('permission.type:kye,view');
    $route->put('kye/{id}', [KyeController::class, 'update'])
        ->middleware('permission.type:kye,update');
    $route->delete('kye/{id}', [KyeController::class, 'destroy'])
        ->middleware('permission.type:kye,delete');

    $route->get('kye-address', [KyeAddressController::class, 'index'])
        ->middleware('permission.type:kye-address,view');
    $route->post('kye-address', [KyeAddressController::class, 'store'])
        ->middleware('permission.type:kye-address,create');
    $route->get('kye-address/{id}', [KyeAddressController::class, 'show'])
        ->middleware('permission.type:kye-address,view');
    $route->put('kye-address/{id}', [KyeAddressController::class, 'update'])
        ->middleware('permission.type:kye-address,update');
    $route->delete('kye-address/{id}', [KyeAddressController::class, 'destroy'])
        ->middleware('permission.type:kye-address,delete');

    $route->get('kye-education', [KyeEducationController::class, 'index'])
        ->middleware('permission.type:kye-education,view');
    $route->post('kye-education', [KyeEducationController::class, 'store'])
        ->middleware('permission.type:kye-education,create');
    $route->get('kye-education/{id}', [KyeEducationController::class, 'show'])
        ->middleware('permission.type:kye-education,view');
    $route->put('kye-education/{id}', [KyeEducationController::class, 'update'])
        ->middleware('permission.type:kye-education,update');
    $route->delete('kye-education/{id}', [KyeEducationController::class, 'destroy'])
        ->middleware('permission.type:kye-education,delete');

    $route->get('kye-emergency-contact', [KyeEmergencyContactController::class, 'index'])
        ->middleware('permission.type:kye-emergency-contact,view');
    $route->post('kye-emergency-contact', [KyeEmergencyContactController::class, 'store'])
        ->middleware('permission.type:kye-emergency-contact,create');
    $route->get('kye-emergency-contact/{id}', [KyeEmergencyContactController::class, 'show'])
        ->middleware('permission.type:kye-emergency-contact,view');
    $route->put('kye-emergency-contact/{id}', [KyeEmergencyContactController::class, 'update'])
        ->middleware('permission.type:kye-emergency-contact,update');
    $route->delete('kye-emergency-contact/{id}', [KyeEmergencyContactController::class, 'destroy'])
        ->middleware('permission.type:kye-emergency-contact,delete');

    $route->get('kye-experience', [KyeExperienceController::class, 'index'])
        ->middleware('permission.type:kye-experience,view');
    $route->post('kye-experience', [KyeExperienceController::class, 'store'])
        ->middleware('permission.type:kye-experience,create');
    $route->get('kye-experience/{id}', [KyeExperienceController::class, 'show'])
        ->middleware('permission.type:kye-experience,view');
    $route->put('kye-experience/{id}', [KyeExperienceController::class, 'update'])
        ->middleware('permission.type:kye-experience,update');
    $route->delete('kye-experience/{id}', [KyeExperienceController::class, 'destroy'])
        ->middleware('permission.type:kye-experience,delete');

    $route->get('kye-service', [KyeServiceController::class, 'index'])
        ->middleware('permission.type:kye-service,view');
    $route->post('kye-service', [KyeServiceController::class, 'store'])
        ->middleware('permission.type:kye-service,create');
    $route->get('kye-service/{id}', [KyeServiceController::class, 'show'])
        ->middleware('permission.type:kye-service,view');
    $route->put('kye-service/{id}', [KyeServiceController::class, 'update'])
        ->middleware('permission.type:kye-service,update');
    $route->delete('kye-service/{id}', [KyeServiceController::class, 'destroy'])
        ->middleware('permission.type:kye-service,delete');

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
