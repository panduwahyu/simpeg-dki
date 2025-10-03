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
        //
        DB::table('users')->insert([
            [
                'NIP' => '123456789',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'name' => 'Admin User',
                'role' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'NIP' => '987654321',
                'email' => 'pegawai@example.com',
                'password' => Hash::make('password'),
                'name' => 'Pegawai User',
                'role' => 'Pegawai',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
