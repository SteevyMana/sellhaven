<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            ['name' => 'Admin User',     'email' => 'admin@example.com',  'password' => Hash::make('admin123'),  'role_id' => 1, 'status' => 'active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jane Manager',   'email' => 'jane@example.com',   'password' => Hash::make('jane123'),   'role_id' => 2, 'status' => 'active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Luis Manager',   'email' => 'luis@example.com',   'password' => Hash::make('luis123'),   'role_id' => 2, 'status' => 'active',   'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carlos Cashier', 'email' => 'carlos@example.com', 'password' => Hash::make('carlos123'), 'role_id' => 3, 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
