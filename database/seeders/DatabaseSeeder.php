<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun uji coba untuk Guru
        User::factory()->create([
            'name' => 'Guru RPP AI',
            'email' => 'guru@gmail.com',
            'password' => bcrypt('password123'), 
        ]);

        // 2. Akun uji coba untuk Superadmin (TAMBAHKAN BAGIAN INI)
        User::factory()->create([
            'name' => 'Superadmin RPP',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password123'), 
        ]);

        // 3. Memanggil Seeder Batasan 4C & Dalil
        $this->call([
            RppAppSeeder::class,
        ]);
    }
}