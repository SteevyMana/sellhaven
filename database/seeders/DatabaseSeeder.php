<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
        ]);

        // Sincronizar secuencias de PostgreSQL
        Artisan::call('db:sync-sequences');

        // Agregar permisos completos y sincronizar roles
        $this->call(AdditionalPermissionsSeeder::class);
    }
}