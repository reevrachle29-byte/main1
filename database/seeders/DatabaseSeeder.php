<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Office;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default hashed password
        $defaultPassword = Hash::make('password123');

        // 1. Administrator Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@cpac.edu.ph'],
            [
                'name' => 'CPAC System Admin',
                'contact' => '09123456789',
                'role' => 'Administrator',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        // 2. Employee Accounts (Staff)
        $registrarStaff = User::firstOrCreate(
            ['email' => 'registrar@cpac.edu.ph'],
            [
                'name' => 'Registrar Staff',
                'contact' => '09123456788',
                'role' => 'Employee',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        $financeStaff = User::firstOrCreate(
            ['email' => 'business@cpac.edu.ph'],
            [
                'name' => 'Business Office Staff',
                'contact' => '09123456785',
                'role' => 'Employee',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        // 3. Student Account
        User::firstOrCreate(
            ['email' => 'student@cpac.edu.ph'],
            [
                'name' => 'Student User',
                'contact' => '09123456787',
                'role' => 'Student',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        // 4. Campus Offices
        $registrarOffice = Office::firstOrCreate(
            ['name' => 'SSO Office'],
            [
                'user_id' => $registrarStaff->user_id,
                'is_active' => true,
            ]
        );

        $businessOffice = Office::firstOrCreate(
            ['name' => 'Business Office'],
            [
                'user_id' => $financeStaff->user_id,
                'is_active' => true,
            ]
        );

        // 5. Office Services
        Service::firstOrCreate(
            [
                'office_id' => $registrarOffice->office_id,
                'service_name' => 'Transcript of Records (TOR)',
            ],
            [
                'user_id' => $registrarStaff->user_id,
                'is_active' => true,
            ]
        );

        Service::firstOrCreate(
            [
                'office_id' => $registrarOffice->office_id,
                'service_name' => 'Certificate of Enrollment',
            ],
            [
                'user_id' => $registrarStaff->user_id,
                'is_active' => true,
            ]
        );

        Service::firstOrCreate(
            [
                'office_id' => $businessOffice->office_id,
                'service_name' => 'Tuition Payment & Assessment',
            ],
            [
                'user_id' => $financeStaff->user_id,
                'is_active' => true,
            ]
        );
    }
}