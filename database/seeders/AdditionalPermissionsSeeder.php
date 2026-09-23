<?php
// database/seeders/AdditionalPermissionsSeeder.php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AdditionalPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Todos los permisos del sistema ───────────────────────
        // firstOrCreate — no duplica si ya existe
        $all = [
            // Products
            ['name' => 'products.view',         'module' => 'Products',      'description' => 'View product list and details'],
            ['name' => 'products.create',        'module' => 'Products',      'description' => 'Create new products'],
            ['name' => 'products.edit',          'module' => 'Products',      'description' => 'Edit existing products'],
            ['name' => 'products.delete',        'module' => 'Products',      'description' => 'Delete products'],
            // Categories
            ['name' => 'categories.manage',      'module' => 'Categories',    'description' => 'Create, edit and delete categories'],
            // Suppliers
            ['name' => 'suppliers.manage',       'module' => 'Suppliers',     'description' => 'Create, edit and delete suppliers'],
            // Purchases
            ['name' => 'purchases.view',         'module' => 'Purchases',     'description' => 'View purchase orders'],
            ['name' => 'purchases.manage',       'module' => 'Purchases',     'description' => 'Create, edit and delete purchase orders'],
            // Stock Entries
            ['name' => 'stock-entries.view',     'module' => 'Stock Entries', 'description' => 'View stock entries'],
            ['name' => 'stock-entries.manage',   'module' => 'Stock Entries', 'description' => 'Create and manage stock entries'],
            // Returns
            ['name' => 'returns.view',           'module' => 'Returns',       'description' => 'View returns to supplier'],
            ['name' => 'returns.manage',         'module' => 'Returns',       'description' => 'Create and manage returns to supplier'],
            // Orders
            ['name' => 'orders.view',            'module' => 'Orders',        'description' => 'View orders'],
            ['name' => 'orders.create',          'module' => 'Orders',        'description' => 'Create new orders'],
            ['name' => 'orders.edit',            'module' => 'Orders',        'description' => 'Edit orders'],
            ['name' => 'orders.delete',          'module' => 'Orders',        'description' => 'Delete orders'],
            // Customers
            ['name' => 'customers.view',         'module' => 'Customers',     'description' => 'View customer list and details'],
            ['name' => 'customers.manage',       'module' => 'Customers',     'description' => 'Create, edit and delete customers'],
            // Sales
            ['name' => 'sales.view',             'module' => 'Sales',         'description' => 'View sales / POS'],
            ['name' => 'sales.manage',           'module' => 'Sales',         'description' => 'Create and manage sales'],
            // Payments
            ['name' => 'payments.view',          'module' => 'Payments',      'description' => 'View payments'],
            ['name' => 'payments.manage',        'module' => 'Payments',      'description' => 'Create and manage payments'],
            // Reports
            ['name' => 'reports.view',           'module' => 'Reports',       'description' => 'View reports and analytics'],
            // Security
            ['name' => 'users.manage',           'module' => 'Users',         'description' => 'Create, edit and delete users'],
            ['name' => 'roles.manage',           'module' => 'Roles',         'description' => 'Create, edit and delete roles'],
            ['name' => 'settings.manage',        'module' => 'Settings',      'description' => 'Manage system-wide settings and permissions'],
       
            ['name' => 'customer-returns.view',   'module' => 'Customer Returns', 'description' => 'View customer returns'],
            ['name' => 'customer-returns.manage', 'module' => 'Customer Returns', 'description' => 'Create, edit and delete customer returns'],
       
            ['name' => 'audit.view', 'module' => 'Security', 'description' => 'View the system activity log'],
            ];

        // Crear permisos que no existen
        $created = collect($all)->map(function ($data) {
            return Permission::firstOrCreate(
                ['name' => $data['name']],
                ['module' => $data['module'], 'description' => $data['description']]
            );
        });

        // ── Asignar permisos a roles ─────────────────────────────
        $admin   = Role::where('name', 'Administrator')->first();
        $manager = Role::where('name', 'Manager')->first();
        $cashier = Role::where('name', 'Cashier')->first();

        // Administrator → todos
        if ($admin) {
            $admin->permissions()->sync($created->pluck('id')->toArray());
        }

        // Manager → inventario + ventas + compras + reportes (sin seguridad)
        if ($manager) {
            $managerPerms = $created->filter(fn($p) =>
                !in_array($p->name, ['users.manage', 'roles.manage', 'settings.manage'])
            )->pluck('id')->toArray();
            $manager->permissions()->sync($managerPerms);
        }

        // Cashier → solo lo esencial en caja
        if ($cashier) {
            $cashierPerms = $created->filter(fn($p) =>
                in_array($p->name, [
                    'products.view',
                    'orders.view', 'orders.create',
                    'customers.view',
                    'sales.view', 'sales.manage',
                    'payments.view',
                ])
            )->pluck('id')->toArray();
            $cashier->permissions()->sync($cashierPerms);
        }

        $this->command->info('✓ Permissions synced: ' . $created->count() . ' total');
        $this->command->info('✓ Administrator: all permissions');
        $this->command->info('✓ Manager: all except security');
        $this->command->info('✓ Cashier: POS only');
    }
}