<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            ['name' => 'Laptop',     'price' => 1200, 'stock' => 10, 'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mouse',      'price' => 50,   'stock' => 25, 'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Keyboard',   'price' => 80,   'stock' => 15, 'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Monitor',    'price' => 400,  'stock' => 8,  'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Headphones', 'price' => 150,  'stock' => 20, 'category_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
