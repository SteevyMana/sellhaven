<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->insert([
            ['name' => 'TechDistrib S.A.',   'contact' => 'Carlos Méndez', 'email' => 'carlos@techdistrib.com', 'phone' => '809-555-0101', 'city' => 'Santiago',      'category' => 'Electrónica', 'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Importaciones Ruiz', 'contact' => 'Ana Ruiz',      'email' => 'ana@importruiz.com',     'phone' => '809-555-0102', 'city' => 'Santo Domingo', 'category' => 'Ropa',        'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Deportes del Norte', 'contact' => 'Luis Peña',     'email' => 'luis@deportesnorte.com', 'phone' => '809-555-0103', 'city' => 'Santiago',      'category' => 'Deportes',    'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hogar Total',        'contact' => 'María Santos',  'email' => 'maria@hogartotal.com',   'phone' => '809-555-0104', 'city' => 'La Vega',       'category' => 'Hogar',       'status' => 'Inactive', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Global Electronics', 'contact' => 'Pedro Vargas',  'email' => 'pedro@globalelec.com',   'phone' => '809-555-0105', 'city' => 'Puerto Plata',  'category' => 'Electrónica', 'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
