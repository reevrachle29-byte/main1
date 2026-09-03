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
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Queue session already open for this office.');
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

    public function close(Request $request)
    {
        $this->authorizeOffice($request->office_id);

        $session = QueueSession::where('office_id', $request->office_id)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'No open queue session found.');
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
            ->where('status', 'open')
            ->first();

        return response()->json([
            'is_open' => (bool) $session,
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
