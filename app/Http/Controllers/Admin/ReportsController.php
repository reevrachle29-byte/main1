<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueRequest;
use App\Models\QueueTransaction;
use App\Models\AuditLog;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $totalToday = QueueRequest::whereDate('requested_at', $today)->count();
        $completedToday = QueueRequest::where('status', 'completed')
            ->whereHas('transaction', fn ($query) => $query->whereDate('completed_at', $today))
            ->count();
        $waitingNow = QueueRequest::where('status', 'waiting')->count();
        $avgWaitMinutes = QueueTransaction::whereDate('completed_at', $today)
            ->whereNotNull('wait_minutes')
            ->avg('wait_minutes');

        $busiestService = QueueRequest::query()
            ->join('services', 'queue_requests.service_id', '=', 'services.service_id')
            ->whereDate('queue_requests.requested_at', $today)
            ->select('services.service_name as name', DB::raw('COUNT(queue_requests.request_id) as total'))
            ->groupBy('services.service_name', 'services.service_id')
            ->orderByDesc('total')
            ->first();

        $stats = [
            'totalToday' => $totalToday,
            'completedToday' => $completedToday,
            'waitingNow' => $waitingNow,
            'avgWaitMinutes' => $avgWaitMinutes,
            'activeOffices' => Office::where('is_active', true)->count(),
            'completionRate' => $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0,
            'busiestServiceName' => $busiestService?->name ?? '—',
            'busiestServiceTotal' => $busiestService?->total ?? 0,
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

    public function export(Request $request)
    {
        $query = QueueRequest::query()
            ->with(['service.office', 'user'])
            ->orderBy('requested_at', 'desc');

        if ($request->filled('office_id')) {
            $query->whereHas('service', fn ($q) => $q->where('office_id', $request->office_id));
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

        $rows = $query->get();

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['request_id', 'queue_number', 'tracking_code', 'status', 'category', 'office', 'service', 'user', 'requested_at', 'completed_at']);

        foreach ($rows as $row) {
            $requestedAt = $row->requested_at ? Carbon::parse($row->requested_at)->toDateTimeString() : null;
            $completedAt = $row->transaction && $row->transaction->completed_at
                ? Carbon::parse($row->transaction->completed_at)->toDateTimeString()
                : null;

            fputcsv($csv, [
                $row->request_id,
                $row->queue_number,
                $row->tracking_code,
                $row->status,
                $row->category,
                $row->service?->office?->name ?? '—',
                $row->service?->service_name ?? '—',
                $row->user?->name ?? 'Walk-in',
                $requestedAt,
                $completedAt,
            ]);
        }

        rewind($csv);
        $contents = stream_get_contents($csv);
        fclose($csv);

        $filename = 'queue-report-' . now()->format('YmdHis') . '.csv';

        return response($contents, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
