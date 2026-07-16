<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Demo Pelanggan
        User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@heavyrent.test',
            'password' => Hash::make('customer123'),
        ]);

        // 2. Buat Akun Demo Admin
        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@heavyrent.test',
            'password' => Hash::make('admin123'),
        ]);
        
        // Panggil seeder Excavator atau Operator di bawah ini jika ada, contoh:
        // $this->call(ExcavatorSeeder::class);
    }
}