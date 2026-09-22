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

        $officeBreakdownQuery = QueueRequest::query()
            ->join('services', 'queue_requests.service_id', '=', 'services.service_id')
            ->join('offices', 'services.office_id', '=', 'offices.office_id')
            ->select('offices.office_id', 'offices.name')
            ->selectRaw('COUNT(queue_requests.request_id) as total')
            ->selectRaw("SUM(CASE WHEN queue_requests.status = 'waiting' THEN 1 ELSE 0 END) as waiting")
            ->selectRaw("SUM(CASE WHEN queue_requests.status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN queue_requests.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw("SUM(CASE WHEN queue_requests.status = 'skipped' THEN 1 ELSE 0 END) as skipped")
            ->when($request->filled('office_id'), fn ($report) => $report->where('services.office_id', $request->office_id))
            ->when($request->filled('date_from'), fn ($report) => $report->whereDate('queue_requests.requested_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($report) => $report->whereDate('queue_requests.requested_at', '<=', $request->date_to))
            ->when($request->filled('status'), fn ($report) => $report->where('queue_requests.status', $request->status))
            ->groupBy('offices.office_id', 'offices.name')
            ->orderByDesc('total');

        $officeBreakdown = $officeBreakdownQuery->get();

        $statusBreakdown = (clone $query)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

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
            'officeBreakdown' => $officeBreakdown,
            'statusBreakdown' => $statusBreakdown,
            'stats' => $stats,
            'offices' => $offices,
            'recentAuditLogs' => $recentAuditLogs,
            'filters' => $request->only(['office_id', 'date_from', 'date_to', 'status']),
        ]);
    }
}
