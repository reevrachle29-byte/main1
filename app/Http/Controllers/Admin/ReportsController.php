<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueRequest;
use App\Models\QueueTransaction;
use App\Models\AuditLog;
use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = QueueRequest::with(['service.office', 'user', 'transaction.staff']);

        if ($request->filled('office_id')) {
            $query->whereHas('service', fn($q) => $q->where('office_id', $request->office_id));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $queueRequests = $query->orderBy('requested_at', 'desc')->paginate(25)->withQueryString();

        $today = Carbon::today();
        $stats = [
            'totalToday' => QueueRequest::whereDate('requested_at', $today)->count(),
            'completedToday' => QueueRequest::where('status', 'completed')
                ->whereHas('transaction', fn ($query) => $query->whereDate('completed_at', $today))
                ->count(),
            'waitingNow' => QueueRequest::where('status', 'waiting')->count(),
            'avgWaitMinutes' => QueueTransaction::whereDate('completed_at', $today)
                ->whereNotNull('wait_minutes')
                ->avg('wait_minutes'),
        ];

        $offices = Office::where('is_active', true)->select('office_id', 'name')->get();

        $recentAuditLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return Inertia::render('Admin/Reports/Index', [
            'queueRequests' => $queueRequests,
            'stats' => $stats,
            'offices' => $offices,
            'recentAuditLogs' => $recentAuditLogs,
            'filters' => $request->only(['office_id', 'date_from', 'date_to', 'status']),
        ]);
    }
}
