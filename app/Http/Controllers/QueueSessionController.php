<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueueSession;
use App\Models\Office;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class QueueSessionController extends Controller
{
    public function open(Request $request)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,office_id',
        ]);

        $this->authorizeOffice($request->office_id);

        $existing = QueueSession::where('office_id', $request->office_id)
            ->whereIn('status', ['open', 'paused'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Queue session is already active or paused for this office.');
        }

        QueueSession::create([
            'office_id' => $request->office_id,
            'user_id' => Auth::id(),
            'status' => 'open',
            'opened_at' => Carbon::now(),
        ]);

        Office::where('office_id', $request->office_id)->update([
            'is_active' => true,
        ]);

        \App\Models\Service::where('office_id', $request->office_id)->update([
            'is_active' => true,
        ]);

        AuditLog::log('queue_session_opened', 'Opened queue session for office #' . $request->office_id);

        return redirect()->back()->with('success', 'Queue session opened.');
    }

    public function pause(Request $request)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,office_id',
        ]);

        $this->authorizeOffice($request->office_id);

        $session = QueueSession::where('office_id', $request->office_id)
            ->whereIn('status', ['open', 'paused'])
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'No active queue session found for this office.');
        }

        $nextStatus = $session->status === 'paused' ? 'open' : 'paused';
        $session->update([
            'status' => $nextStatus,
            'closed_at' => null,
        ]);

        Office::where('office_id', $request->office_id)->update([
            'is_active' => $nextStatus === 'open',
        ]);

        \App\Models\Service::where('office_id', $request->office_id)->update([
            'is_active' => $nextStatus === 'open',
        ]);

        AuditLog::log('queue_session_' . ($nextStatus === 'paused' ? 'paused' : 'resumed'), 'Queue session ' . ($nextStatus === 'paused' ? 'paused' : 'resumed') . ' for office #' . $request->office_id);

        return redirect()->back()->with('success', $nextStatus === 'paused' ? 'Queue session paused.' : 'Queue session resumed.');
    }

    public function close(Request $request)
    {
        $this->authorizeOffice($request->office_id);

        $session = QueueSession::where('office_id', $request->office_id)
            ->whereIn('status', ['open', 'paused'])
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'No active queue session found.');
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => Carbon::now(),
        ]);

        Office::where('office_id', $request->office_id)->update([
            'is_active' => false,
        ]);

        \App\Models\Service::where('office_id', $request->office_id)->update([
            'is_active' => false,
        ]);

        AuditLog::log('queue_session_closed', 'Closed queue session for office #' . $request->office_id);

        return redirect()->back()->with('success', 'Queue session closed.');
    }

    public function status(Request $request)
    {
        $officeId = $request->office_id;
        $session = QueueSession::where('office_id', $officeId)
            ->whereIn('status', ['open', 'paused'])
            ->first();

        return response()->json([
            'is_open' => $session?->status === 'open',
            'is_paused' => $session?->status === 'paused',
            'status' => $session?->status,
            'session' => $session,
        ]);
    }

    private function authorizeOffice($officeId): void
    {
        $user = Auth::user();

        $isAdmin = in_array(strtolower((string) $user->role), ['admin', 'administrator'], true);

        if ($isAdmin) {
            return;
        }

        abort_unless((int) $user->office_id === (int) $officeId, 403);
    }
}
