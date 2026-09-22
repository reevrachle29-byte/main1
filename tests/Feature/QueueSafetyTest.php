<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\QueueRequest;
use App\Models\QueueSession;
use App\Models\QueueTransaction;
use App\Models\Notification;
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

    public function test_student_dashboard_shows_priority_aware_queue_position_and_wait_estimate(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $office = Office::create(['name' => 'Student Services', 'is_active' => true]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Enrollment Help',
            'is_active' => true,
        ]);
        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $staff->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $completed = QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 100,
            'tracking_code' => 'QV-DONE01',
            'status' => 'completed',
            'category' => 'regular',
            'requested_at' => now()->subHour(),
        ]);
        QueueTransaction::create([
            'request_id' => $completed->request_id,
            'served_by' => $staff->user_id,
            'called_at' => now()->subMinutes(50),
            'completed_at' => now()->subMinutes(40),
            'wait_minutes' => 12,
        ]);

        QueueRequest::create([
            'user_id' => $staff->user_id,
            'service_id' => $service->service_id,
            'queue_number' => 101,
            'tracking_code' => 'QV-AHEAD1',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now()->subMinutes(10),
        ]);
        $studentTicket = QueueRequest::create([
            'user_id' => $student->user_id,
            'service_id' => $service->service_id,
            'queue_number' => 102,
            'tracking_code' => 'QV-STUD01',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('myQueueRequests.0.request_id', $studentTicket->request_id)
            ->where('myQueueRequests.0.queue_position', 2)
            ->where('myQueueRequests.0.estimated_wait_minutes', 24)
        );
    }

    public function test_queue_accepts_only_real_system_categories(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $office = Office::create([
            'name' => 'Priority Office',
            'user_id' => $staff->user_id,
            'is_active' => true,
        ]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Document Processing',
            'is_active' => true,
        ]);
        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $staff->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($student)->from('/kiosk')->post('/kiosk/generate', [
            'service_id' => $service->service_id,
            'category' => 'priority',
        ]);

        $response->assertSessionHasErrors(['category']);
        $this->assertDatabaseMissing('queue_requests', [
            'service_id' => $service->service_id,
            'user_id' => $student->user_id,
        ]);
    }

    public function test_new_student_ticket_notifies_the_assigned_office(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $staff = User::factory()->create(['role' => 'staff']);
        $office = Office::create([
            'name' => 'SSO Office',
            'user_id' => $staff->user_id,
            'is_active' => true,
        ]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Residence Inquiry',
            'is_active' => true,
        ]);
        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $staff->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($student)->post('/kiosk/generate', [
            'service_id' => $service->service_id,
            'category' => 'regular',
        ]);

        $this->assertDatabaseHas('queue_requests', [
            'service_id' => $service->service_id,
            'queue_number' => 100,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->user_id,
            'type' => 'office',
            'message' => 'Ticket #100 joined the Residence Inquiry queue.',
        ]);
        $this->assertSame(1, Notification::where('user_id', $staff->user_id)->where('type', 'office')->count());
    }
}
