<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\Service;
use App\Models\QueueRequest;
use App\Models\QueueSession;
use App\Models\Notification;
use App\Models\AuditLog;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function showKiosk()
    {
        $openSessionOfficeIds = QueueSession::where('status', 'open')
            ->pluck('office_id')
            ->toArray();

        $offices = Office::where('is_active', true)
            ->whereIn('office_id', $openSessionOfficeIds)
            ->with('services')
            ->get();

        return Inertia::render('Queue/Kiosk', [
            'offices' => $offices
        ]);
    }

    public function generateTicket(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,service_id',
            'category' => 'required|in:pwd,senior,regular'
        ]);

        $service = Service::with('office')->findOrFail($request->service_id);

        abort_unless($service->office?->is_active, 404);

        $session = QueueSession::where('office_id', $service->office_id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'Queue is not currently open for this office.');
        }

        $currentUser = Auth::user();

        if ($currentUser && !$currentUser->isStudent()) {
            $redirectRoute = $currentUser->isEmployee() && $currentUser->office_id
                ? route('dashboard.staff', ['officeId' => $currentUser->office_id])
                : route('dashboard');

            return redirect()->to($redirectRoute)->with('error', 'Only student accounts can request queue tickets.');
        }

        if ($currentUser) {
            $activeTicketCount = QueueRequest::where('user_id', $currentUser->user_id)
                ->whereIn('status', ['waiting', 'called', 'serving'])
                ->count();

            if ($activeTicketCount >= 2) {
                return redirect()->back()->with('error', 'You can only have up to 2 active tickets at a time.');
            }
        }

        $latestTicket = QueueRequest::whereDate('requested_at', today())->max('queue_number');
        $nextNumber = $latestTicket ? $latestTicket + 1 : 100;

        $trackingCode = 'QV-' . strtoupper(Str::random(6));

        $newQueue = QueueRequest::create([
            'user_id' => $currentUser?->user_id,
            'service_id' => $request->service_id,
            'queue_number' => $nextNumber,
            'tracking_code' => $trackingCode,
            'status' => 'waiting',
            'category' => $request->category,
            'requested_at' => now(),
        ]);

        AuditLog::log('queue_ticket_generated', "Ticket #{$nextNumber} generated", [
            'request_id' => $newQueue->request_id,
            'tracking_code' => $trackingCode,
        ]);

        $position = QueueRequest::where('service_id', $request->service_id)
            ->where('status', 'waiting')
            ->where('requested_at', '<=', $newQueue->requested_at)
            ->count();

        $avgWait = QueueRequest::where('service_id', $request->service_id)
            ->where('status', 'completed')
            ->with('transaction')
            ->get()
            ->pluck('transaction.wait_minutes')
            ->filter()
            ->avg();

        $estimatedWait = $position > 0 && $avgWait ? round($position * $avgWait) : null;

        return redirect()->back()
            ->with('success', "Ticket #{$nextNumber} generated.")
            ->with('ticket', [
                'request_id' => $newQueue->request_id,
                'queue_number' => $nextNumber,
                'tracking_code' => $trackingCode,
                'position' => $position,
                'estimated_wait' => $estimatedWait,
                'service' => $service->service_name,
                'office' => $service->office?->name,
            ]);
    }

    public function cancelTicket($requestId)
    {
        $ticket = QueueRequest::where('request_id', $requestId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($ticket->status, ['waiting', 'called', 'serving'], true)) {
            return redirect()->back()->with('error', 'Only active tickets can be cancelled.');
        }

        $ticket->update(['status' => 'cancelled']);

        AuditLog::log('queue_cancel_student', "Student cancelled ticket #{$ticket->queue_number}", [
            'request_id' => $ticket->request_id,
        ]);

        return redirect()->route('dashboard')->with('info', "Ticket #{$ticket->queue_number} cancelled.");
    }

    public function manualGenerate(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,service_id',
            'category' => 'required|in:pwd,senior,regular'
        ]);

        $service = Service::with('office')->findOrFail($request->service_id);

        abort_unless(in_array($service->service_id, $this->accessibleServiceIds(Auth::user()), true), 403);

        $session = QueueSession::where('office_id', $service->office_id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'Queue is not currently open for this office.');
        }

        $latestTicket = QueueRequest::whereDate('requested_at', today())->max('queue_number');
        $nextNumber = $latestTicket ? $latestTicket + 1 : 100;
        $trackingCode = 'QV-' . strtoupper(Str::random(6));

        $category = $request->input('category', 'regular');

        $newQueue = QueueRequest::create([
            'user_id' => null,
            'service_id' => $request->service_id,
            'queue_number' => $nextNumber,
            'tracking_code' => $trackingCode,
            'status' => 'waiting',
            'category' => $category,
            'requested_at' => now(),
        ]);

        AuditLog::log('queue_ticket_manual', "Walk-in ticket #{$nextNumber} generated", [
            'request_id' => $newQueue->request_id,
        ]);

        return redirect()->back()->with('success', "Walk-in ticket #{$nextNumber} created — Tracking: {$trackingCode}");
    }

    public function showDisplayMonitor()
    {
        $activeTickets = QueueRequest::where('status', 'called')
            ->with(['service.office'])
            ->latest('requested_at')
            ->take(6)
            ->get();

        $waitingTickets = QueueRequest::where('status', 'waiting')
            ->with(['service.office'])
            ->orderBy('requested_at', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Queue/Monitor', [
            'activeTickets' => $activeTickets,
            'waitingTickets' => $waitingTickets,
        ]);
    }

    public function inquiry(Request $request)
    {
        $result = null;
        $position = null;
        $estimatedWait = null;

        if ($request->filled('tracking_code')) {
            [$result, $position, $estimatedWait] = $this->queueLookup($request->input('tracking_code'));
        }

        return Inertia::render('Queue/Inquiry', compact('result', 'position', 'estimatedWait'));
    }

    public function searchByTrackingCode(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string',
        ]);

        [$queueRequest, $position, $estimatedWait] = $this->queueLookup($request->tracking_code);

        if (!$queueRequest) {
            return redirect()->back()->with('error', 'No queue request found with that tracking code.');
        }

        return Inertia::render('Queue/Inquiry', [
            'result' => $queueRequest,
            'position' => $queueRequest->status === 'waiting' ? $position : null,
            'estimatedWait' => $estimatedWait,
        ]);
    }

    private function queueLookup(string $trackingCode): array
    {
        $queueRequest = QueueRequest::where('tracking_code', $trackingCode)
            ->with(['service.office', 'transaction'])
            ->first();

        if (!$queueRequest) {
            return [null, null, null];
        }

        $position = QueueRequest::where('service_id', $queueRequest->service_id)
            ->where('status', 'waiting')
            ->where('requested_at', '<', $queueRequest->requested_at)
            ->count() + 1;

        $avgWait = QueueRequest::where('service_id', $queueRequest->service_id)
            ->where('status', 'completed')
            ->with('transaction')
            ->get()
            ->pluck('transaction.wait_minutes')
            ->filter()
            ->avg();

        return [$queueRequest, $queueRequest->status === 'waiting' ? $position : null, $queueRequest->status === 'waiting' && $avgWait ? round($position * $avgWait) : null];
    }

    private function accessibleServiceIds($user): array
    {
        if ($user->isAdmin()) {
            return Service::query()->pluck('service_id')->all();
        }

        if (!$user->office_id) {
            return [];
        }

        return Service::where('office_id', $user->office_id)->pluck('service_id')->all();
    }
}
