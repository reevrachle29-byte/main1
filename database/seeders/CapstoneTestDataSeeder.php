<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Office;
use App\Models\Service;
use App\Models\QueueRequest;
use App\Models\QueueTransaction;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CapstoneTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Mock Users (Admin, Staff, and Student)
        $admin = User::create([
            'name' => 'Simple Jay (Admin)',
            'email' => 'admin@cpac.edu',
            'password' => Hash::make('password123'),
            'contact' => '09123456789',
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Kleah Joy (Staff)',
            'email' => 'staff@cpac.edu',
            'password' => Hash::make('password123'),
            'contact' => '09987654321',
            'role' => 'staff',
        ]);

        $student = User::create([
            'name' => 'John Doe (Student)',
            'email' => 'johndoe@gmail.com',
            'password' => Hash::make('password123'),
            'contact' => '09555444333',
            'role' => 'student',
        ]);

        // 2. Create Mock Offices
        $registrar = Office::create([
            'user_id' => $admin->user_id,
            'name' => 'SSO Office',
            'is_active' => true,
        ]);

        $finance = Office::create([
            'user_id' => $admin->user_id,
            'name' => 'Finance Department',
            'is_active' => true,
        ]);

        // 3. Create Mock Services under those Offices
        $serviceTranscript = Service::create([
            'office_id' => $registrar->office_id,
            'user_id' => $admin->user_id,
            'service_name' => 'Transcript of Records Request',
            'is_active' => true,
        ]);

        $serviceTuition = Service::create([
            'office_id' => $finance->office_id,
            'user_id' => $admin->user_id,
            'service_name' => 'Tuition Fee Payment',
            'is_active' => true,
        ]);

        // 4. Create a Mock Queue Request (Student grabs a ticket)
        $request = QueueRequest::create([
            'user_id' => $student->user_id,
            'service_id' => $serviceTranscript->service_id,
            'queue_number' => 101,
            'status' => 'completed',
            'requested_at' => Carbon::now()->subMinutes(15),
        ]);

        // 5. Create a Mock Queue Transaction (Staff serves the student)
        $transaction = QueueTransaction::create([
            'request_id' => $request->request_id,
            'served_by' => $staff->user_id,
            'called_at' => Carbon::now()->subMinutes(10),
            'completed_at' => Carbon::now(),
            'wait_minutes' => 5,
        ]);

        // 6. Create Mock Audit Notifications
        Notification::create([
            'transaction_id' => $transaction->transaction_id,
            'type' => 'display',
            'message' => 'Ticket 101 called at Registrar Window 1',
            'sent_at' => Carbon::now()->subMinutes(10),
        ]);

        Notification::create([
            'transaction_id' => $transaction->transaction_id,
            'type' => 'audio',
            'message' => 'Now serving ticket 101 at the Registrar Office',
            'sent_at' => Carbon::now()->subMinutes(10),
        ]);
    }
}