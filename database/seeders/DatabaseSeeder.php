<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Idempotent: aman dijalankan berkali-kali (Railway menjalankan seeder
        // di setiap deploy), tidak akan membuat duplikat / error unique constraint.
        User::firstOrCreate(
            ['email' => 'admin@heavyrent.test'],
            [
                'name' => 'Admin HeavyRent',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@heavyrent.test'],
            [
                'name' => 'Customer Demo',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]
        );
    }
}
