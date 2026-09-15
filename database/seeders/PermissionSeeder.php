<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            ['name' => 'customer', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'customer', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'customer', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'customer', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'customer', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'supplier', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'supplier', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'supplier', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'supplier', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'supplier', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'user', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'user', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'user', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'user', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'user', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'sales', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'sales', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'sales', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'sales', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'sales-return', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'sales-return', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'sales-return', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'sales-return', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'purchase', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'purchase', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'purchase', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'purchase', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'purchase-return', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'purchase-return', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'purchase-return', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'purchase-return', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'cheque', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'cheque', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'cheque', 'type' => 'clear', 'guard_name' => 'web'],
            ['name' => 'cheque', 'type' => 'cancel', 'guard_name' => 'web'],
            ['name' => 'cheque', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'cheque', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'cheque', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'bank', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'bank', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'bank', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'bank', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'bank', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'credit', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'credit', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'credit', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'credit', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'payment', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'payment', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'payment', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'payment', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'loan', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'loan', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'loan', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'loan', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'loan', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'loan-payment', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'loan-payment', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'loan-payment', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'loan-payment', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'loan-payment', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'employee', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'employee', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'employee', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'employee', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'employee', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'salary', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'salary', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'salary', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'salary', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'product', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'product', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'product', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'product', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'procurement', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'procurement', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'procurement', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'procurement', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'scheme', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'scheme', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'scheme', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'scheme', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'scheme-payment', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'scheme-payment', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'scheme-payment', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'scheme-payment', 'type' => 'delete', 'guard_name' => 'web'],

            ['name' => 'expenses', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'expenses', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'expenses', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'expenses', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'expenses', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'kye', 'type' => 'view', 'guard_name' => 'web'],
            ['name' => 'kye', 'type' => 'create', 'guard_name' => 'web'],
            ['name' => 'kye', 'type' => 'update', 'guard_name' => 'web'],
            ['name' => 'kye', 'type' => 'delete', 'guard_name' => 'web'],
            ['name' => 'kye', 'type' => 'search', 'guard_name' => 'web'],

            ['name' => 'report', 'type' => 'view', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'type' => $permission['type'],
                    'guard_name' => $permission['guard_name'],
                ]
            );
        }
    }
}
