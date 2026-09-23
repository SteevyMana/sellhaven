<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            ['name' => 'Maria Lopez',  'email' => 'maria@email.com',  'phone' => '809-111-0001', 'city' => 'Santiago',      'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Juan Perez',   'email' => 'juan@email.com',   'phone' => '809-111-0002', 'city' => 'Santo Domingo', 'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ana Gomez',    'email' => 'ana@email.com',    'phone' => '809-111-0003', 'city' => 'La Vega',       'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Luis Torres',  'email' => 'luis@email.com',   'phone' => '809-111-0004', 'city' => 'Santiago',      'status' => 'Inactive', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carmen Ruiz',  'email' => 'carmen@email.com', 'phone' => '809-111-0005', 'city' => 'Moca',          'status' => 'Active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pedro Santos', 'email' => 'pedro@email.com',  'phone' => '809-111-0006', 'city' => 'Puerto Plata',  'status' => 'Inactive', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
