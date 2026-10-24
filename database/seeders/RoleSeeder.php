<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Define roles and create one demo user per role.
     *
     *  - superadmin => gets ALL permissions + superadmin@admin.com
     *  - admin      => gets all except role/permission management + admin@admin.com
     *  - manager    => gets operational permissions + manager@admin.com
     */
    public function run(): void
    {
        // ================================================================
        // 1. Create roles
        // ================================================================
        $superadmin = Role::updateOrCreate(
            ['slug' => 'superadmin'],
            [
                'name'        => 'Super Admin',
                'description' => 'Full access to everything. Cannot be restricted.',
                'status'      => 1,
            ]
        );

        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name'        => 'Admin',
                'description' => 'Administrative access. Manages users, warehouse, shipments, billing.',
                'status'      => 1,
            ]
        );

        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name'        => 'Manager',
                'description' => 'Operational access. Handles daily shipment, warehouse, and billing tasks.',
                'status'      => 1,
            ]
        );

        // ================================================================
        // 2. Super Admin : ALL permissions
        // ================================================================
        $allPermissionIds = Permission::pluck('id')->toArray();
        $superadmin->permissions()->sync($allPermissionIds);
        $this->command->info('Super Admin assigned ' . count($allPermissionIds) . ' permissions.');

        // ================================================================
        // 3. Admin : ALL permissions (same as Super Admin, but can still be restricted by editing the role)
        // ================================================================
        $adminPermissionIds = Permission::pluck('id')->toArray();
        $admin->permissions()->sync($adminPermissionIds);
        $this->command->info('Admin assigned ' . count($adminPermissionIds) . ' permissions.');

        // ================================================================
        // 4. Manager : operational groups only
        // ================================================================
        $managerGroups = [
            'Dashboard',
            'Customer',
            'Warehouse',
            'Shipment',
            'Shipment Cost',
            'Carton',
            'Price',
            'Shipment Status',
            'Billing',
            'Return',
            'Shipping Mark',
            'Agents',
            'Cost Type',
            'Customer Notification',
            'Office Expense',
            'Ledger',
            'Reports',
        ];

        $managerExcludedSlugs = [
            'delete-users',
            'create-users',
            'edit-users',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'bulk-create-permissions',
        ];

        $managerPermissionIds = Permission::whereIn('group_name', $managerGroups)
            ->whereNotIn('slug', $managerExcludedSlugs)
            ->pluck('id')
            ->toArray();

        $manager->permissions()->sync($managerPermissionIds);
        $this->command->info('Manager assigned ' . count($managerPermissionIds) . ' permissions.');

        // ================================================================
        // 5. Create one user per role
        // ================================================================
        $superadminUser = User::firstOrCreate(
            ['email' => 'superadmin@admin.com'],
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@admin.com',
                'password' => Hash::make('superadmin@admin.com'),
                'role'     => 2,                    // legacy admin flag
                'role_id'  => $superadmin->id,
                'status'   => 1,
            ]
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'     => 'Admin User',
                'email'    => 'admin@admin.com',
                'password' => Hash::make('admin@admin.com'),
                'role'     => 2,                    // legacy admin flag
                'role_id'  => $admin->id,
                'status'   => 1,
            ]
        );

        $managerUser = User::firstOrCreate(
            ['email' => 'manager@admin.com'],
            [
                'name'     => 'Manager',
                'email'    => 'manager@admin.com',
                'password' => Hash::make('manager@admin.com'),
                'role'     => 1,                    // legacy regular-user flag
                'role_id'  => $manager->id,
                'status'   => 1,
            ]
        );

        // ================================================================
        // 6. Summary
        // ================================================================
        $this->command->info('');
        $this->command->info('=== Roles Seeded ===');
        $this->command->table(
            ['Role', 'Slug', 'Status', 'Permissions'],
            [
                ['Super Admin', $superadmin->slug, 'Active', $superadmin->permissions()->count()],
                ['Admin',       $admin->slug,       'Active', $admin->permissions()->count()],
                ['Manager',     $manager->slug,     'Active', $manager->permissions()->count()],
            ]
        );

        $this->command->info('');
        $this->command->info('=== Users Seeded ===');
        $this->command->table(
            ['Name', 'Email', 'Password', 'Role', 'Status'],
            [
                ['Super Admin', 'superadmin@admin.com', 'superadmin@admin.com', 'Super Admin', 'Active'],
                ['Admin User',  'admin@admin.com',       'admin@admin.com',       'Admin',       'Active'],
                ['Manager',     'manager@admin.com',     'manager@admin.com',     'Manager',     'Active'],
            ]
        );

        $this->command->info('');
        $this->command->info('✅ 3 roles + 3 users seeded successfully.');
    }
}