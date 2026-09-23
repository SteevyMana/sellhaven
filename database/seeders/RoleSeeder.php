<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Administrator', 'description' => 'Full access to every module in the system', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Manager',       'description' => 'Manages inventory, orders and customers',    'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Cashier',       'description' => 'Point of sale — view products and create orders', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
