<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /* ----------------------------------------------------------
         | 1. Super Admin bypass
         | If the user's role slug is 'superadmin', allow everything.
         | Returning null falls through to normal gate checks.
         * ---------------------------------------------------------- */
        Gate::before(function ($user, $ability) {
            if ($user->roleRelation && $user->roleRelation->slug === 'superadmin') {
                return true;
            }
            return null;
        });

        /* ----------------------------------------------------------
         | 2. Explicit permission gates
         | Any slug defined here becomes usable with:
         |     @can('create-users')   /   Gate::allows('edit-roles')
         |     ->middleware('can:view-shipments')
         * ---------------------------------------------------------- */
        $permissions = [

            // -------- Dashboard --------
            'view-dashboard',

            // -------- User Management --------
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'toggle-user-status',

            // -------- Role Management --------
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'toggle-role-status',

            // -------- Permission Management --------
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'bulk-create-permissions',

            // -------- Customer --------
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // -------- Warehouse --------
            'manage-warehouse',
            'view-warehouse',
            'add-warehouse-item',
            'edit-warehouse-item',
            'delete-warehouse-item',
            'view-warehouse-item',
            'import-warehouse-file',

            // -------- Shipment --------
            'view-shipments',
            'create-shipments',
            'edit-shipments',
            'delete-shipments',
            'shipment-report',
            'shipment-summary',

            // -------- Shipment Cost --------
            'view-shipment-cost',
            'add-shipment-cost',
            'edit-shipment-cost',
            'delete-shipment-cost',
            'add-bill-payment',      // pay one or many shipment costs in one voucher
            'delete-bill-payment',

            // -------- Carton (CTN) --------
            'view-ctn',
            'edit-ctn',
            'print-ctn',

            // -------- Price --------
            'view-price',
            'edit-price',

            // -------- Shipment Status --------
            'manage-shipment-status',
            'status-ongoing',
            'status-shipped',
            'status-arrived-bd',
            'status-waiting-for-custom',
            'status-delivered',
            'status-cancelled',

            // -------- Billing / Invoice / Payment --------
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'delete-invoices',
            'view-payment-history',
            'add-payment',
            'edit-payment',
            'delete-payment',

            // -------- Return --------
            'view-returns',
            'add-returns',
            'edit-returns',
            'delete-returns',

            // -------- Shipping Mark --------
            'view-shipping-mark',
            'add-shipping-mark',
            'edit-shipping-mark',
            'delete-shipping-mark',

            // -------- Agents --------
            'view-agents',
            'add-agents',
            'edit-agents',
            'delete-agents',

            // -------- Cost Type --------
            'view-cost-types',
            'add-cost-types',
            'edit-cost-types',
            'delete-cost-types',

            // -------- Old Data --------
            'view-old-data',
            'add-old-data',
            'edit-old-data',
            'delete-old-data',
            'manage-old-data',

            // -------- Customer Notification --------
            'view-notifications',
            'manage-notifications',

            // -------- Office & Expense (separate from shipments) --------
            'view-office-expense',
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }
}