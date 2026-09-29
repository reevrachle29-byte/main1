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
use Illuminate\Support\Facades\DB;
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

    public function test_walk_in_kiosk_is_scoped_to_the_selected_office_without_changing_main_kiosk(): void
    {
        $registrar = Office::create(['name' => 'Registrar Office', 'is_active' => true]);
        $business = Office::create(['name' => 'Business Office', 'is_active' => true]);

        Service::create([
            'office_id' => $registrar->office_id,
            'service_name' => 'Transcript Request',
            'is_active' => true,
        ]);
        Service::create([
            'office_id' => $business->office_id,
            'service_name' => 'Payment',
            'is_active' => true,
        ]);

        $this->get('/kiosk/walk-in/' . $registrar->office_id)
            ->assertInertia(fn ($page) => $page
                ->component('Queue/Kiosk')
                ->where('walk_in_mode', true)
                ->where('walk_in_office_id', $registrar->office_id)
                ->has('offices', 1)
                ->where('offices.0.office_id', $registrar->office_id)
            );

        $this->get('/kiosk')
            ->assertInertia(fn ($page) => $page
                ->component('Queue/Kiosk')
                ->where('walk_in_mode', false)
                ->has('offices', 2)
            );
    }

    public function test_walk_in_kiosk_cannot_issue_a_ticket_for_another_office(): void
    {
        $registrar = Office::create(['name' => 'Registrar Office', 'is_active' => true]);
        $business = Office::create(['name' => 'Business Office', 'is_active' => true]);
        $businessService = Service::create([
            'office_id' => $business->office_id,
            'service_name' => 'Payment',
            'is_active' => true,
        ]);

        $this->post('/kiosk/walk-in/' . $registrar->office_id . '/generate', [
            'service_id' => $businessService->service_id,
            'category' => 'regular',
        ])->assertNotFound();

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
            ->where('myQueueRequests.0.estimated_wait_minutes', 6)
        );
    }

    public function test_student_dashboard_avoids_n_plus_one_queue_lookups(): void
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

        foreach ([
            ['queue_number' => 100, 'status' => 'waiting', 'category' => 'regular', 'requested_at' => now()->subMinutes(40), 'user_id' => $student->user_id],
            ['queue_number' => 101, 'status' => 'waiting', 'category' => 'regular', 'requested_at' => now()->subMinutes(30), 'user_id' => $student->user_id],
            ['queue_number' => 102, 'status' => 'waiting', 'category' => 'regular', 'requested_at' => now()->subMinutes(20), 'user_id' => $student->user_id],
            ['queue_number' => 103, 'status' => 'waiting', 'category' => 'senior', 'requested_at' => now()->subMinutes(10), 'user_id' => $student->user_id],
        ] as $ticket) {
            QueueRequest::create([
                'user_id' => $ticket['user_id'],
                'service_id' => $service->service_id,
                'queue_number' => $ticket['queue_number'],
                'tracking_code' => 'QV-'.str_pad((string) $ticket['queue_number'], 5, '0', STR_PAD_LEFT),
                'status' => $ticket['status'],
                'category' => $ticket['category'],
                'requested_at' => $ticket['requested_at'],
            ]);
        }

        DB::enableQueryLog();
        $response = $this->actingAs($student)->get('/dashboard');
        $response->assertOk();

        $this->assertLessThanOrEqual(12, count(DB::getQueryLog()));
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

    public function test_staff_can_pause_and_resume_a_queue_session(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'office_id' => null,
        ]);
        $office = Office::create([
            'name' => 'Pause Office',
            'user_id' => $staff->user_id,
            'is_active' => true,
        ]);
        $staff->update(['office_id' => $office->office_id]);
        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $staff->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $pauseResponse = $this->actingAs($staff)->post('/queue-session/pause', [
            'office_id' => $office->office_id,
        ]);

        $pauseResponse->assertSessionHas('success', 'Queue session paused.');
        $this->assertDatabaseHas('queue_sessions', [
            'office_id' => $office->office_id,
            'status' => 'paused',
        ]);

        $resumeResponse = $this->actingAs($staff)->post('/queue-session/pause', [
            'office_id' => $office->office_id,
        ]);

        $resumeResponse->assertSessionHas('success', 'Queue session resumed.');
        $this->assertDatabaseHas('queue_sessions', [
            'office_id' => $office->office_id,
            'status' => 'open',
        ]);
    }

    public function test_display_monitor_shows_current_queue_status_and_next_ticket(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $office = Office::create([
            'name' => 'Lobby Desk',
            'user_id' => $staff->user_id,
            'is_active' => true,
        ]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Document Release',
            'is_active' => true,
        ]);
        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $staff->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $calledTicket = QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 100,
            'tracking_code' => 'QV-CALL01',
            'status' => 'called',
            'category' => 'regular',
            'requested_at' => now()->subMinutes(5),
        ]);
        QueueTransaction::create([
            'request_id' => $calledTicket->request_id,
            'served_by' => $staff->user_id,
            'called_at' => now()->subMinutes(2),
            'counter_number' => 2,
        ]);

        $nextTicket = QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 101,
            'tracking_code' => 'QV-NEXT01',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now()->subMinutes(3),
        ]);

        $response = $this->get('/monitor');

        $response->assertInertia(fn ($page) => $page
            ->component('Queue/Monitor')
            ->where('officeStatus', 'Open')
            ->where('nextTicket.queue_number', $nextTicket->queue_number)
            ->where('activeTickets.0.queue_number', $calledTicket->queue_number)
            ->where('activeTickets.0.transaction.counter_number', 2)
        );
    }

    public function test_staff_call_next_assigns_selected_window_and_office_monitor_filters_tickets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $office = Office::create(['name' => 'Registrar', 'window_count' => 3, 'is_active' => true]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Transcript Request',
            'is_active' => true,
        ]);
        $ticket = QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 101,
            'tracking_code' => 'QV-WINDOW1',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now(),
        ]);

        $otherOffice = Office::create(['name' => 'Finance', 'is_active' => true]);
        $otherService = Service::create([
            'office_id' => $otherOffice->office_id,
            'service_name' => 'Payment',
            'is_active' => true,
        ]);
        $otherTicket = QueueRequest::create([
            'service_id' => $otherService->service_id,
            'queue_number' => 102,
            'tracking_code' => 'QV-WINDOW2',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now()->subMinute(),
        ]);

        $this->actingAs($admin)->post('/dashboard/staff/call-next', [
            'office_id' => $office->office_id,
            'counter_number' => 2,
        ])->assertSessionHas('success', 'Ticket #101 called.');

        $this->assertDatabaseHas('queue_transactions', [
            'request_id' => $ticket->request_id,
            'counter_number' => 2,
        ]);
        $this->assertDatabaseHas('queue_requests', [
            'request_id' => $otherTicket->request_id,
            'status' => 'waiting',
        ]);

        $this->get('/monitor/' . $office->office_id)->assertInertia(fn ($page) => $page
            ->component('Queue/Monitor')
            ->where('officeName', 'Registrar')
            ->where('activeTickets.0.transaction.counter_number', 2)
            ->where('nextTicket', null)
        );
    }

    public function test_admin_can_call_next_without_selecting_an_office(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $office = Office::create(['name' => 'Admissions', 'window_count' => 2, 'is_active' => true]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Application Review',
            'is_active' => true,
        ]);
        $ticket = QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 109,
            'tracking_code' => 'QV-ADMIN01',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now(),
        ]);

        $this->actingAs($admin)->post('/dashboard/staff/call-next', [
            'counter_number' => 1,
        ])->assertSessionHas('success', 'Ticket #109 called.');

        $this->assertDatabaseHas('queue_transactions', [
            'request_id' => $ticket->request_id,
            'counter_number' => 1,
        ]);
    }

    public function test_admin_can_export_filtered_queue_report_as_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $office = Office::create(['name' => 'Finance Office', 'is_active' => true]);
        $service = Service::create([
            'office_id' => $office->office_id,
            'service_name' => 'Cashier Help',
            'is_active' => true,
        ]);
        QueueRequest::create([
            'service_id' => $service->service_id,
            'queue_number' => 100,
            'tracking_code' => 'QV-EXPORT1',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/export?office_id=' . $office->office_id . '&status=waiting');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertSeeText('queue_number');
        $response->assertSeeText('QV-EXPORT1');
    }
}
