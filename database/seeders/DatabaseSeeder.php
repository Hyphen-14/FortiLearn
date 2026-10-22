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
        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@secureacademy.test'],
            [
                'name' => 'Security Administrator',
                'password' => 'PasswordAdmin123!',
                'role' => 'admin',
                'balance' => 0.00,
                'is_active' => true,
            ]
        );

        // 2. Instructor Account
        $instructor = User::firstOrCreate(
            ['email' => 'instructor@secureacademy.test'],
            [
                'name' => 'Prof. Cyber Instructor',
                'password' => 'PasswordInstruktur123!',
                'role' => 'instructor',
                'balance' => 0.00,
                'is_active' => true,
            ]
        );

        // 3. Student Account with Wallet Balance
        $student1 = User::firstOrCreate(
            ['email' => 'student1@secureacademy.test'],
            [
                'name' => 'Alice Student',
                'password' => 'PasswordStudent123!',
                'role' => 'student',
                'balance' => 500000.00, // Rp 500.000 saldo awal
                'is_active' => true,
            ]
        );

        $student2 = User::firstOrCreate(
            ['email' => 'student2@secureacademy.test'],
            [
                'name' => 'Bob Student',
                'password' => 'PasswordStudent123!',
                'role' => 'student',
                'balance' => 100000.00, // Rp 100.000 saldo awal
                'is_active' => true,
            ]
        );
    }
}
