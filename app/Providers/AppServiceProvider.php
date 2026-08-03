<?php

namespace App\Providers;

use App\Contracts\InfrastructureConfigurationRepository;
use App\Models\Tenant\Cheque\Cheque;
use App\Models\Tenant\Credit\Credit;
use App\Models\Tenant\Customer\Customer;
use App\Models\Tenant\Invoice\Return\InvoiceReturn;
use App\Models\Tenant\Payment\Payment;
use App\Models\Tenant\Purchase\Order\PurchaseOrder;
use App\Models\Tenant\Purchase\Return\PurchaseReturn;
use App\Models\Tenant\Supplier\Supplier;
use App\Models\Tenant\User\User;
use App\Repositories\Infrastructure\EloquentInfrastructureConfigurationRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            InfrastructureConfigurationRepository::class,
            EloquentInfrastructureConfigurationRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'user' => User::class,
            'supplier' => Supplier::class,
            'customer' => Customer::class,
            'payment' => Payment::class,
            'cheque' => Cheque::class,
            'purchase_order' => PurchaseOrder::class,
            'purchase_return' => PurchaseReturn::class,
            'credit' => Credit::class,
            'invoice_return' => InvoiceReturn::class,
        ]);
    }
}
