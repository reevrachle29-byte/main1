<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\QueueRequest;
use App\Models\Service;
use App\Models\QueueSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_administrators_are_redirected_to_the_admin_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'Administrator',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_employee_without_an_assigned_office_is_redirected_away_from_the_staff_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'Employee',
            'office_id' => null,
        ]);

        \App\Models\Office::create([
            'name' => 'SSO Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard/staff');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_employees_with_an_assigned_office_are_redirected_to_the_staff_dashboard_from_the_student_dashboard_route(): void
    {
        $office = Office::create([
            'name' => 'Registrar Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'Employee',
            'office_id' => $office->office_id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('dashboard.staff', ['officeId' => $office->office_id], absolute: false));
    }

    public function test_employee_without_office_assignment_is_redirected_to_the_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'Employee',
            'office_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/dashboard/staff');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_employee_can_view_only_their_assigned_office_dashboard(): void
    {
        $officeOne = \App\Models\Office::create([
            'name' => 'Registrar Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $officeTwo = \App\Models\Office::create([
            'name' => 'SSO Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'Employee',
            'office_id' => $officeOne->office_id,
        ]);

        QueueSession::create([
            'office_id' => $officeOne->office_id,
            'user_id' => $user->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        QueueSession::create([
            'office_id' => $officeTwo->office_id,
            'user_id' => $user->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard/staff/' . $officeOne->office_id);

        $response->assertOk();
        $response->assertSee('Registrar Office');
        $response->assertDontSee('SSO Office');

        $redirectResponse = $this->actingAs($user)->get('/dashboard/staff/' . $officeTwo->office_id);

        $redirectResponse->assertRedirect(route('dashboard.staff', ['officeId' => $user->office_id], absolute: false));
    }

    public function test_closing_a_queue_session_keeps_the_office_visible(): void
    {
        $office = Office::create([
            'name' => 'Cashier Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->office_id,
            'user_id' => null,
            'service_name' => 'Cash Release',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'Administrator',
        ]);

        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $user->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->from('/dashboard/staff/' . $office->office_id)
            ->post('/queue-session/close', [
                'office_id' => $office->office_id,
            ]);

        $response->assertRedirect('/dashboard/staff/' . $office->office_id);
        $response->assertSessionHas('success', 'Queue session closed.');

        $this->assertDatabaseHas('queue_sessions', [
            'office_id' => $office->office_id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('offices', [
            'office_id' => $office->office_id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('services', [
            'service_id' => $service->service_id,
            'is_active' => false,
        ]);
    }

    public function test_staff_dashboard_keeps_offices_visible_even_when_they_are_inactive(): void
    {
        $office = Office::create([
            'name' => 'Cashier Office',
            'user_id' => null,
            'is_active' => false,
        ]);

        $user = User::factory()->create([
            'role' => 'Administrator',
        ]);

        $response = $this->actingAs($user)->get('/dashboard/staff/' . $office->office_id);

        $response->assertOk();
        $response->assertSee('Cashier Office');
    }

    public function test_admin_user_management_lists_all_offices_for_staff_assignment(): void
    {
        Office::create([
            'name' => 'Registrar Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        Office::create([
            'name' => 'Payroll Office',
            'user_id' => null,
            'is_active' => false,
        ]);

        $admin = User::factory()->create([
            'role' => 'Administrator',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Registrar Office');
        $response->assertSee('Payroll Office');
    }

    public function test_admin_dashboard_counts_completed_tickets_by_completion_date(): void
    {
        $office = Office::create([
            'name' => 'Finance Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->office_id,
            'user_id' => null,
            'service_name' => 'Tuition Payment',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'Administrator']);

        $ticket = QueueRequest::create([
            'user_id' => $student->user_id,
            'service_id' => $service->service_id,
            'queue_number' => 300,
            'tracking_code' => 'QV-COMPLETE1',
            'status' => 'completed',
            'category' => 'regular',
            'requested_at' => now()->subDay(),
        ]);

        \App\Models\QueueTransaction::create([
            'request_id' => $ticket->request_id,
            'served_by' => $admin->user_id,
            'called_at' => now()->subMinutes(15),
            'completed_at' => now(),
            'wait_minutes' => 15,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $this->assertSame(1, \App\Models\QueueRequest::where('status', 'completed')
            ->whereHas('transaction', fn ($query) => $query->whereDate('completed_at', today()))
            ->count());
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_guest_can_create_a_queue_ticket_without_an_account(): void
    {
        $office = Office::create([
            'name' => 'Registrar Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->office_id,
            'user_id' => null,
            'service_name' => 'Enrollment Inquiry',
            'is_active' => true,
        ]);

        $sessionOwner = User::factory()->create(['role' => 'Administrator']);

        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $sessionOwner->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->from('/')->post('/kiosk/generate', [
            'service_id' => $service->service_id,
            'category' => 'regular',
        ]);

        $response->assertSessionHas('success', 'Ticket #100 generated.');
        $this->assertDatabaseHas('queue_requests', [
            'user_id' => null,
            'service_id' => $service->service_id,
            'status' => 'waiting',
            'category' => 'regular',
        ]);
    }

    public function test_employee_cannot_create_a_queue_ticket(): void
    {
        $office = Office::create([
            'name' => 'Registrar Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->office_id,
            'user_id' => null,
            'service_name' => 'Enrollment Inquiry',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'Employee',
            'office_id' => $office->office_id,
        ]);

        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $user->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($user)->from('/dashboard/staff/' . $office->office_id)->post('/kiosk/generate', [
            'service_id' => $service->service_id,
            'category' => 'regular',
        ]);

        $response->assertRedirect('/dashboard/staff/' . $office->office_id);
        $response->assertSessionHas('error', 'Only student accounts can request queue tickets.');
        $this->assertDatabaseMissing('queue_requests', ['user_id' => $user->user_id]);
    }

    public function test_student_cannot_create_more_than_two_active_tickets(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $office = Office::create([
            'name' => 'SSO Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->office_id,
            'user_id' => null,
            'service_name' => 'Document Request',
            'is_active' => true,
        ]);

        QueueSession::create([
            'office_id' => $office->office_id,
            'user_id' => $user->user_id,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/kiosk/generate', [
                'service_id' => $service->service_id,
                'category' => 'regular',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post('/kiosk/generate', [
                'service_id' => $service->service_id,
                'category' => 'regular',
            ])
            ->assertSessionHasNoErrors();

        $response = $this->actingAs($user)->from('/dashboard')->post('/kiosk/generate', [
            'service_id' => $service->service_id,
            'category' => 'regular',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'You can only have up to 2 active tickets at a time.');
        $this->assertEquals(2, QueueRequest::where('user_id', $user->user_id)->whereIn('status', ['waiting', 'called', 'serving'])->count());
    }

    public function test_student_can_cancel_their_own_ticket(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $office = Office::create([
            'name' => 'Registrar Office',
            'user_id' => null,
            'is_active' => true,
        ]);

        $service = Service::create([
            'office_id' => $office->office_id,
            'user_id' => null,
            'service_name' => 'Enrollment Inquiry',
            'is_active' => true,
        ]);

        $queueRequest = QueueRequest::create([
            'user_id' => $user->user_id,
            'service_id' => $service->service_id,
            'queue_number' => 101,
            'tracking_code' => 'QV-TEST01',
            'status' => 'waiting',
            'category' => 'regular',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/queue/cancel/' . $queueRequest->request_id);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('info', 'Ticket #101 cancelled.');
        $this->assertDatabaseHas('queue_requests', [
            'request_id' => $queueRequest->request_id,
            'status' => 'cancelled',
        ]);
    }
}
