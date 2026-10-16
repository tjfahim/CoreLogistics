<?php
// database/seeders/PermissionSeeder.php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Full permission map.
     * Key   = group_name
     * Value = array of permission names (slug auto-generated).
     */
    protected array $permissions = [

        // ---------- Dashboard ----------
        'Dashboard' => [
            'View Dashboard',
        ],

        // ---------- User Management ----------
        'User Management' => [
            'View Users',
            'Create Users',
            'Edit Users',
            'Delete Users',
            'Toggle User Status',
        ],

        // ---------- Role Management ----------
        'Role Management' => [
            'View Roles',
            'Create Roles',
            'Edit Roles',
            'Delete Roles',
            'Toggle Role Status',
        ],

        // ---------- Permission Management ----------
        'Permission Management' => [
            'View Permissions',
            'Create Permissions',
            'Edit Permissions',
            'Delete Permissions',
            'Bulk Create Permissions',
        ],

        // ---------- Customer ----------
        'Customer' => [
            'View Customers',
            'Create Customers',
            'Edit Customers',
            'Delete Customers',
        ],

        // ---------- Warehouse ----------
        'Warehouse' => [
            'Manage Warehouse',
            'View Warehouse',
            'Add Warehouse Item',
            'Edit Warehouse Item',
            'Delete Warehouse Item',
            'View Warehouse Item',
            'Import Warehouse File',
        ],

        // ---------- Shipment ----------
        'Shipment' => [
            'View Shipments',
            'Create Shipments',
            'Edit Shipments',
            'Delete Shipments',
            'Shipment Report',
            'Shipment Summary',
        ],

        // ---------- Shipment Cost ----------
        'Shipment Cost' => [
            'View Shipment Cost',
            'Add Shipment Cost',
            'Edit Shipment Cost',
            'Delete Shipment Cost',
            'Add Bill Payment',
            'Delete Bill Payment',
        ],

        // ---------- CTN (Carton) ----------
        'Carton' => [
            'View CTN',
            'Edit CTN',
            'Print CTN',
        ],

        // ---------- Price ----------
        'Price' => [
            'View Price',
            'Edit Price',
        ],

        // ---------- Shipment Status ----------
        'Shipment Status' => [
            'Manage Shipment Status',
            'Status Ongoing',
            'Status Shipped',
            'Status Arrived BD',
            'Status Waiting For Custom',
            'Status Delivered',
            'Status Cancelled',
        ],

        // ---------- Billing / Invoice ----------
        'Billing' => [
            'View Invoices',
            'Create Invoices',
            'Edit Invoices',
            'Delete Invoices',
            'View Payment History',
            'Add Payment',
            'Edit Payment',
            'Delete Payment',
        ],

        // ---------- Return ----------
        'Return' => [
            'View Returns',
            'Add Returns',
            'Edit Returns',
            'Delete Returns',
        ],

        // ---------- Shipping Mark ----------
        'Shipping Mark' => [
            'View Shipping Mark',
            'Add Shipping Mark',
            'Edit Shipping Mark',
            'Delete Shipping Mark',
        ],

        // ---------- Agents ----------
        'Agents' => [
            'View Agents',
            'Add Agents',
            'Edit Agents',
            'Delete Agents',
        ],

        // ---------- Cost Type ----------
        'Cost Type' => [
            'View Cost Types',
            'Add Cost Types',
            'Edit Cost Types',
            'Delete Cost Types',
        ],

        // ---------- Old Data ----------
        'Old Data' => [
            'View Old Data',
            'Add Old Data',
            'Edit Old Data',
            'Delete Old Data',
            'Manage Old Data',
        ],
    ];

    public function run(): void
    {
        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($this->permissions as $groupName => $names) {
            foreach ($names as $name) {
                $slug = Str::slug($name);

                $exists = Permission::where('slug', $slug)->exists();
                if ($exists) {
                    $totalSkipped++;
                    continue;
                }

                Permission::create([
                    'name'       => $name,
                    'slug'       => $slug,
                    'group_name' => $groupName,
                ]);
                $totalCreated++;
            }
        }

        $this->command->info("✅ Permissions seeded: {$totalCreated} created, {$totalSkipped} skipped.");
    }
}