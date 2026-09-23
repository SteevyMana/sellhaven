<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Electrónica', 'description' => 'Productos electrónicos y tecnología', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Ropa',        'description' => 'Productos de vestimenta',              'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Deportes',    'description' => 'Productos deportivos',                 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Hogar',       'description' => null,                                    'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
