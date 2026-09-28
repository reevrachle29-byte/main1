<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueueRequest;
use App\Models\QueueTransaction;
use App\Models\QueueSession;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Office;
use App\Models\Service;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function showStudentDashboard()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isEmployee()) {
            if ($user->office_id) {
                return redirect()->route('dashboard.staff', ['officeId' => $user->office_id]);
            }
        }

        $notifications = Notification::where('user_id', $user->user_id)
            ->latest('sent_at')
            ->take(5)
            ->get();

        $myQueueRequests = QueueRequest::where('user_id', $user->user_id)
            ->with('service.office')
            ->orderBy('requested_at', 'desc')
            ->get();

        $waitingServiceIds = $myQueueRequests
            ->where('status', 'waiting')
            ->pluck('service_id')
            ->unique()
            ->values();

        $waitingPositions = [];

        if (!$waitingServiceIds->isEmpty()) {
            $waitingTickets = QueueRequest::whereIn('service_id', $waitingServiceIds)
                ->where('status', 'waiting')
                ->orderBy('service_id')
                ->orderByRaw("CASE WHEN category IN ('pwd', 'senior') THEN 1 ELSE 2 END ASC")
                ->orderBy('requested_at', 'asc')
                ->get();

            foreach ($waitingTickets as $ticket) {
                $waitingPositions[$ticket->request_id] = $ticket->queue_number;
            }

            $orderedQueueByService = $waitingTickets->groupBy('service_id');
            foreach ($orderedQueueByService as $serviceId => $tickets) {
                $position = 1;
                foreach ($tickets as $ticket) {
                    $waitingPositions[$ticket->request_id] = $position;
                    $position++;
                }
            }
        }

        $averageWaitByService = DB::table('queue_transactions')
            ->join('queue_requests', 'queue_requests.request_id', '=', 'queue_transactions.request_id')
            ->whereIn('queue_requests.service_id', $waitingServiceIds)
            ->where('queue_requests.status', 'completed')
            ->whereNotNull('queue_transactions.wait_minutes')
            ->select('queue_requests.service_id', DB::raw('AVG(queue_transactions.wait_minutes) as avg_wait_minutes'))
            ->groupBy('queue_requests.service_id')
            ->pluck('avg_wait_minutes', 'service_id');

        $myQueueRequests->each(function (QueueRequest $ticket) use ($waitingPositions, $averageWaitByService) {
            if (!in_array($ticket->status, ['waiting', 'called', 'serving'], true)) {
                return;
            }

            if ($ticket->status !== 'waiting') {
                $ticket->queue_position = 0;
                $ticket->estimated_wait_minutes = 0;
                return;
            }

            $queuePosition = (int) ($waitingPositions[$ticket->request_id] ?? 1);
            $ticket->queue_position = $queuePosition;
            $ticket->estimated_wait_minutes = max(3, $queuePosition * 3);

            if (isset($averageWaitByService[$ticket->service_id])) {
                $ticket->estimated_wait_minutes = max($ticket->estimated_wait_minutes, (int) round($queuePosition * (float) $averageWaitByService[$ticket->service_id] / 5));
            }
        });

        $waitingCount = QueueRequest::where('status', 'waiting')->count();
        $servingCount = QueueRequest::whereIn('status', ['called', 'serving'])->count();

        $offices = Office::where('is_active', true)
            ->with('services')
            ->get();

        $openSessionOfficeIds = QueueSession::where('status', 'open')->pluck('office_id')->toArray();
        $offices = $offices->filter(fn($o) => in_array($o->office_id, $openSessionOfficeIds));

        return Inertia::render('Dashboard', [
            'myQueueRequests' => $myQueueRequests,
            'waitingCount' => $waitingCount,
            'servingCount' => $servingCount,
            'offices' => $offices,
            'user' => $user,
            'notifications' => $notifications,
        ]);
    }

    public function showStaffDashboard($officeId = null)
    {
        $user = Auth::user();

        if ($user->isEmployee() && !$user->office_id) {
            return redirect()->route('dashboard')->with('error', 'No office has been assigned to your employee account yet.');
        }

        if ($user->isEmployee()) {
            $targetOfficeId = $officeId ?? $user->office_id;

            if ((int) $targetOfficeId !== (int) $user->office_id) {
                return redirect()->route('dashboard.staff', ['officeId' => $user->office_id])->with('error', 'You can only access your assigned office.');
            }
        } else {
            $targetOfficeId = $officeId;
        }

        $staffOfficeIds = $user->office_id ? [(int) $user->office_id] : [];

        $officeQuery = Office::query();

        if (!$user->isAdmin()) {
            if (empty($staffOfficeIds)) {
                return redirect()->route('dashboard')->with('error', 'No office has been assigned to your employee account yet.');
            }

            $officeQuery->whereIn('office_id', $staffOfficeIds);
        }

        if ($targetOfficeId) {
            $officeQuery->where('office_id', (int) $targetOfficeId);
        }

        if (!$user->isAdmin() && $user->office_id) {
            $officeQuery->where('office_id', (int) $user->office_id);
        }

        $offices = $officeQuery->with('services')->get();

        if (!$user->isAdmin() && $user->office_id) {
            $offices = $offices->where('office_id', (int) $user->office_id)->values();
            $officeIds = [(int) $user->office_id];
        } else {
            $officeIds = $offices->pluck('office_id')->toArray();
        }

        $serviceIds = Service::whereIn('office_id', $officeIds)
            ->pluck('service_id')
            ->toArray();

        $openSessions = QueueSession::where('status', 'open')
            ->whereIn('office_id', $officeIds)
            ->get();

        $pausedSessions = QueueSession::where('status', 'paused')
            ->whereIn('office_id', $officeIds)
            ->get();

        $waitingQueue = QueueRequest::where('status', 'waiting')
            ->whereIn('service_id', $serviceIds)
            ->with('service')
            ->orderBy('requested_at', 'asc')
            ->get();

        $servingQueue = QueueRequest::whereIn('status', ['called', 'serving'])
            ->whereIn('service_id', $serviceIds)
            ->with(['service', 'transaction'])
            ->get();

        $recentTicketHistory = QueueRequest::whereIn('service_id', $serviceIds)
            ->whereIn('status', ['completed', 'skipped', 'cancelled'])
            ->with('service')
            ->orderByDesc('requested_at')
            ->take(10)
            ->get();

        $selectedOffice = $targetOfficeId ? $offices->first() : (!$user->isAdmin() ? $offices->first() : null);

        $officeNotifications = Notification::whereIn('user_id', $offices->pluck('user_id')->filter())
            ->where('type', 'office')
            ->latest('sent_at')
            ->take(10)
            ->get();

        return Inertia::render('Dashboard/Staff', [
            'waitingQueue' => $waitingQueue,
            'servingQueue' => $servingQueue,
            'recentTicketHistory' => $recentTicketHistory,
            'offices' => $offices,
            'openSessions' => $openSessions,
            'pausedSessions' => $pausedSessions,
            'selectedOffice' => $selectedOffice,
            'officeNotifications' => $officeNotifications,
        ]);
    }

    public function callNext(Request $request)
    {
        $validated = $request->validate([
            'office_id' => 'nullable|integer|exists:offices,office_id',
            'counter_number' => 'required|integer|min:1',
        ]);
        $serviceIds = $this->accessibleServiceIds(Auth::user());
        if (!empty($validated['office_id'])) {
            $office = Office::findOrFail($validated['office_id']);
            abort_if($validated['counter_number'] > $office->window_count, 422, 'The selected window is not configured for this office.');
            $serviceIds = Service::where('office_id', $office->office_id)
                ->whereIn('service_id', $serviceIds)
                ->pluck('service_id')
                ->all();
        }

        // Priority order: PWD > Senior > Regular
        // Within each category, order by requested_at (FIFO)
        [$nextTicket, $transaction] = DB::transaction(function () use ($serviceIds, $validated) {
            $nextTicket = QueueRequest::where('status', 'waiting')
                ->whereIn('service_id', $serviceIds)
                ->with('service.office')
                ->orderByRaw("CASE WHEN category IN ('pwd', 'senior') THEN 1 ELSE 2 END")
                ->orderBy('requested_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$nextTicket) {
                return [null, null];
            }

            if ($validated['counter_number'] > ($nextTicket->service?->office?->window_count ?? 0)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'counter_number' => 'The selected window is not configured for this office.',
                ]);
            }

            $nextTicket->update(['status' => 'called']);
            $transaction = QueueTransaction::create([
                'request_id' => $nextTicket->request_id,
                'served_by' => Auth::user()->user_id,
                'called_at' => Carbon::now(),
                'counter_number' => $validated['counter_number'],
            ]);

            return [$nextTicket, $transaction];
        });

        if (!$nextTicket) {
            return redirect()->back()->with('error', 'No clients waiting in line.');
        }

        Notification::create([
            'transaction_id' => $transaction->transaction_id,
            'user_id' => $nextTicket->user_id,
            'type' => 'display',
            'message' => "Now serving ticket #{$nextTicket->queue_number}. Please proceed to Window {$validated['counter_number']}.",
            'sent_at' => Carbon::now(),
        ]);

        Notification::create([
            'transaction_id' => $transaction->transaction_id,
            'user_id' => $nextTicket->user_id,
            'type' => 'audio',
            'message' => "Now serving ticket number {$nextTicket->queue_number}. Please proceed to Window {$validated['counter_number']}.",
            'sent_at' => Carbon::now(),
        ]);

        if ($nextTicket->user_id) {
            Notification::create([
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $nextTicket->user_id,
                'type' => 'user',
                'message' => "Your ticket #{$nextTicket->queue_number} is now being served.",
                'sent_at' => Carbon::now(),
            ]);
        }

        $upcomingTickets = QueueRequest::where('status', 'waiting')
            ->whereIn('service_id', $serviceIds)
            ->orderByRaw("CASE WHEN category IN ('pwd', 'senior') THEN 1 ELSE 2 END")
            ->orderBy('requested_at')
            ->take(5)
            ->get();

        foreach ($upcomingTickets as $upcoming) {
            if ($upcoming->user_id) {
                Notification::create([
                    'transaction_id' => null,
                    'user_id' => $upcoming->user_id,
                    'type' => 'user',
                    'message' => "Your turn is approaching after ticket #{$nextTicket->queue_number}.",
                    'sent_at' => Carbon::now(),
                ]);
            }
        }

        AuditLog::log('queue_call_next', "Called ticket #{$nextTicket->queue_number}", [
            'request_id' => $nextTicket->request_id,
        ]);

        return redirect()->back()->with('success', "Ticket #{$nextTicket->queue_number} called.");
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

    private function accessibleTicket($requestId): QueueRequest
    {
        $ticket = QueueRequest::findOrFail($requestId);

        abort_unless(in_array($ticket->service_id, $this->accessibleServiceIds(Auth::user()), true), 403);

        return $ticket;
    }

    public function recallLast()
    {
        $lastCalled = QueueRequest::where('status', 'called')
            ->whereIn('service_id', $this->accessibleServiceIds(Auth::user()))
            ->orderBy('requested_at', 'desc')
            ->first();

        if (!$lastCalled) {
            return redirect()->back()->with('error', 'No called ticket to recall.');
        }

        $lastCalled->update(['status' => 'waiting']);

        $transaction = QueueTransaction::where('request_id', $lastCalled->request_id)->first();
        if ($transaction) {
            $transaction->delete();
        }

        AuditLog::log('queue_recall', "Recalled ticket #{$lastCalled->queue_number}", [
            'request_id' => $lastCalled->request_id,
        ]);

        return redirect()->back()->with('success', "Ticket #{$lastCalled->queue_number} recalled to waiting.");
    }

    public function completeTransaction($requestId)
    {
        $ticket = $this->accessibleTicket($requestId);
        if (!in_array($ticket->status, ['called', 'serving'], true)) {
            return redirect()->back()->with('error', 'Only called or serving tickets can be completed.');
        }

        $ticket->update(['status' => 'completed']);

        $transaction = QueueTransaction::where('request_id', $requestId)->first();
        if ($transaction) {
            $completedAt = Carbon::now();
            $calledAt = Carbon::parse($transaction->called_at);

            $transaction->update([
                'completed_at' => $completedAt,
                'wait_minutes' => $calledAt->diffInMinutes($completedAt)
            ]);
        }

        AuditLog::log('queue_complete', "Completed ticket #{$ticket->queue_number}", [
            'request_id' => $ticket->request_id,
        ]);

        return redirect()->back()->with('success', "Ticket #{$ticket->queue_number} completed.");
    }

    public function skipTransaction($requestId)
    {
        $ticket = $this->accessibleTicket($requestId);
        if (!in_array($ticket->status, ['called', 'serving'], true)) {
            return redirect()->back()->with('error', 'Only called or serving tickets can be skipped.');
        }

        $ticket->update(['status' => 'skipped']);

        AuditLog::log('queue_skip', "Skipped ticket #{$ticket->queue_number}", [
            'request_id' => $ticket->request_id,
        ]);

        return redirect()->back()->with('info', "Ticket #{$ticket->queue_number} skipped.");
    }

    public function cancelTransaction($requestId)
    {
        $ticket = $this->accessibleTicket($requestId);
        if (!in_array($ticket->status, ['waiting', 'called', 'serving'], true)) {
            return redirect()->back()->with('error', 'Only active tickets can be cancelled.');
        }

        $ticket->update(['status' => 'cancelled']);

        AuditLog::log('queue_cancel', "Cancelled ticket #{$ticket->queue_number}", [
            'request_id' => $ticket->request_id,
        ]);

        return redirect()->back()->with('info', "Ticket #{$ticket->queue_number} cancelled.");
    }
}
