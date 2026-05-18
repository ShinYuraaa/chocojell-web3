<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus admin lama dulu
        DB::table('admin')->truncate();

        // Insert admin baru
        DB::table('admin')->insert([
            [
                'nama' => 'Admin Choco Jell',
                'email' => 'admin@chocojell.com',
                'password' => Hash::make('@dmin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Super Admin',
                'email' => 'superadmin@chocojell.com',
                'password' => Hash::make('5uper123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
