<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if(blank($email) || blank($password)) {
          throw new \Exception('Admin email and password must be set in the config/admin.php file before running the seeder.');
        }

        \App\Models\User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => config('admin.name', 'System Admin'),
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);
    }
}
