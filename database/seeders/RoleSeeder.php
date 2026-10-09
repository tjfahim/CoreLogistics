<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Define roles.
     *
     *  - superadmin  => gets ALL permissions
     *  - admin       => gets all except role/permission management + user delete
     *  - manager     => gets operational permissions (shipment, warehouse, billing, etc.)
     */
    public function run(): void
    {
        // ---------- 1. Create roles ----------
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

        // ---------- 2. Super Admin : ALL permissions ----------
        $allPermissionIds = Permission::pluck('id')->toArray();
        $superadmin->permissions()->sync($allPermissionIds);
        $this->command->info('Super Admin assigned ' . count($allPermissionIds) . ' permissions.');

        // ---------- 3. Admin : everything EXCEPT role & permission management ----------
        $adminExcludedGroups = [
            'Role Management',
            'Permission Management',
        ];

        $adminPermissionIds = Permission::whereNotIn('group_name', $adminExcludedGroups)
            ->pluck('id')
            ->toArray();

        $admin->permissions()->sync($adminPermissionIds);
        $this->command->info('Admin assigned ' . count($adminPermissionIds) . ' permissions.');

        // ---------- 4. Manager : operational groups only ----------
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
        ];

        // Explicitly EXCLUDE these permission slugs from manager
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

        // ---------- 5. Summary ----------
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
    }
}