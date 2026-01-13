<?php

namespace App\Providers;

use App\Models\Tenant\Cheque\Cheque;
use App\Models\Tenant\Payment\Payment;
use App\Models\Tenant\User\User;
use Illuminate\Support\ServiceProvider;
use App\Models\Tenant\Customer\Customer;
use App\Models\Tenant\PurchaseOrder\PurchaseOrder;
use App\Models\Tenant\Supplier\Supplier;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

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
        ]);
    }
}
