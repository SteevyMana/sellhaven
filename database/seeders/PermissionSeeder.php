<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Products
            ['id' => 1,  'name' => 'products.view',       'module' => 'Products',       'description' => 'View product list and details'],
            ['id' => 2,  'name' => 'products.create',     'module' => 'Products',       'description' => 'Create new products'],
            ['id' => 3,  'name' => 'products.edit',       'module' => 'Products',       'description' => 'Edit existing products'],
            ['id' => 4,  'name' => 'products.delete',     'module' => 'Products',       'description' => 'Delete products'],
            // Categories
            ['id' => 5,  'name' => 'categories.manage',   'module' => 'Categories',     'description' => 'Create, edit and delete categories'],
            // Suppliers
            ['id' => 6,  'name' => 'suppliers.manage',    'module' => 'Suppliers',      'description' => 'Create, edit and delete suppliers'],
            // Purchases
            ['id' => 7,  'name' => 'purchases.view',      'module' => 'Purchases',      'description' => 'View purchase orders'],
            ['id' => 8,  'name' => 'purchases.manage',    'module' => 'Purchases',      'description' => 'Create, edit and delete purchase orders'],
            // Stock Entries
            ['id' => 9,  'name' => 'stock-entries.view',  'module' => 'Stock Entries',  'description' => 'View stock entries'],
            ['id' => 10, 'name' => 'stock-entries.manage','module' => 'Stock Entries',  'description' => 'Create and manage stock entries'],
            // Returns
            ['id' => 11, 'name' => 'returns.view',        'module' => 'Returns',        'description' => 'View returns to supplier'],
            ['id' => 12, 'name' => 'returns.manage',      'module' => 'Returns',        'description' => 'Create and manage returns to supplier'],
            // Orders
            ['id' => 13, 'name' => 'orders.view',         'module' => 'Orders',         'description' => 'View orders'],
            ['id' => 14, 'name' => 'orders.create',       'module' => 'Orders',         'description' => 'Create new orders'],
            ['id' => 15, 'name' => 'orders.edit',         'module' => 'Orders',         'description' => 'Edit orders'],
            ['id' => 16, 'name' => 'orders.delete',       'module' => 'Orders',         'description' => 'Delete orders'],
            // Customers
            ['id' => 17, 'name' => 'customers.view',      'module' => 'Customers',      'description' => 'View customer list and details'],
            ['id' => 18, 'name' => 'customers.manage',    'module' => 'Customers',      'description' => 'Create, edit and delete customers'],
            // Sales
            ['id' => 19, 'name' => 'sales.view',          'module' => 'Sales',          'description' => 'View sales / POS'],
            ['id' => 20, 'name' => 'sales.manage',        'module' => 'Sales',          'description' => 'Create and manage sales'],
            // Payments
            ['id' => 21, 'name' => 'payments.view',       'module' => 'Payments',       'description' => 'View payments'],
            ['id' => 22, 'name' => 'payments.manage',     'module' => 'Payments',       'description' => 'Create and manage payments'],
            // Reports
            ['id' => 23, 'name' => 'reports.view',        'module' => 'Reports',        'description' => 'View reports and analytics'],
            // Security
            ['id' => 24, 'name' => 'users.manage',        'module' => 'Users',          'description' => 'Create, edit and delete users'],
            ['id' => 25, 'name' => 'roles.manage',        'module' => 'Roles',          'description' => 'Create, edit and delete roles'],
            ['id' => 26, 'name' => 'settings.manage',     'module' => 'Settings',       'description' => 'Manage system-wide settings and permissions'],
        
            //Audit Logs
            ['name' => 'audit.view', 'module' => 'Security', 'description' => 'View the system activity log'],
        ];

        foreach ($permissions as &$p) {
            $p['created_at'] = now();
            $p['updated_at'] = now();
        }

        DB::table('permissions')->insert($permissions);

        // Administrator → TODOS los permisos
        DB::table('permission_role')->insert(
            collect(range(1, 26))->map(fn ($id) => [
                'role_id' => 1, 'permission_id' => $id,
            ])->toArray()
        );

        // Manager → inventario + ventas + compras + reportes (sin seguridad)
        DB::table('permission_role')->insert(
            collect([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,17,18,19,20,21,22,23])->map(fn ($id) => [
                'role_id' => 2, 'permission_id' => $id,
            ])->toArray()
        );

        // Cashier → solo lo que necesita en caja
        DB::table('permission_role')->insert(
            collect([1,13,14,17,19,20,21])->map(fn ($id) => [
                'role_id' => 3, 'permission_id' => $id,
            ])->toArray()
        );
    }
}