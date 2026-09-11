<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\QueueRequest;
use App\Models\QueueSession;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_services_cannot_receive_tickets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $office = Office::create(['name' => 'Closed Office', 'is_active' => true]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Closed Service',
            'is_active' => false,
        ]);
        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $admin->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->post('/kiosk/generate', [
            'service_id' => $service->service_id,
            'category' => 'regular',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('queue_requests', 0);
    }

    public function test_completed_tickets_cannot_be_completed_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $office = Office::create(['name' => 'Admin Office', 'is_active' => true]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Admin Service',
            'is_active' => true,
        ]);
        $ticket = QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 100,
            'tracking_code' => 'QV-SAFE01',
            'status' => 'completed',
            'category' => 'regular',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/dashboard/staff/complete/' . $ticket->request_id);

        $response->assertSessionHas('error', 'Only called or serving tickets can be completed.');
        $this->assertDatabaseHas('queue_requests', [
            'request_id' => $ticket->request_id,
            'status' => 'completed',
        ]);
    }
}
