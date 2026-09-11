<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QueueSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ReportsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/kiosk', [QueueController::class, 'showKiosk'])->name('queue.kiosk');
Route::post('/kiosk/generate', [QueueController::class, 'generateTicket'])
    ->middleware('throttle:ticket-generation')
    ->name('queue.generate');
Route::get('/monitor', [QueueController::class, 'showDisplayMonitor'])->name('queue.monitor');

Route::get('/queue/inquiry', [QueueController::class, 'inquiry'])
    ->middleware('throttle:tracking-lookup')
    ->name('queue.inquiry');
Route::post('/queue/inquiry/search', [QueueController::class, 'searchByTrackingCode'])
    ->middleware('throttle:tracking-lookup')
    ->name('queue.inquiry.search');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth',
    config('jetstream.auth_session'),
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'showStudentDashboard'])->name('dashboard');
    Route::post('/queue/cancel/{requestId}', [QueueController::class, 'cancelTicket'])->name('queue.cancelOwn');

    /*
    | Staff & Employee Routes
    */
    Route::middleware(['role:Administrator,Employee,Staff'])->group(function () {
        Route::get('/dashboard/staff/{officeId?}', [DashboardController::class, 'showStaffDashboard'])->name('dashboard.staff');

        Route::post('/dashboard/staff/call-next', [DashboardController::class, 'callNext'])->name('queue.callNext');
        Route::post('/dashboard/staff/recall-last', [DashboardController::class, 'recallLast'])->name('queue.recallLast');
        Route::post('/dashboard/staff/complete/{requestId}', [DashboardController::class, 'completeTransaction'])->name('queue.complete');
        Route::post('/dashboard/staff/skip/{requestId}', [DashboardController::class, 'skipTransaction'])->name('queue.skip');
        Route::post('/dashboard/staff/cancel/{requestId}', [DashboardController::class, 'cancelTransaction'])->name('queue.cancel');
        Route::post('/dashboard/staff/manual-generate', [QueueController::class, 'manualGenerate'])->name('queue.manualGenerate');

        Route::post('/queue-session/open', [QueueSessionController::class, 'open'])->name('queueSession.open');
        Route::post('/queue-session/close', [QueueSessionController::class, 'close'])->name('queueSession.close');
        Route::get('/queue-session/status', [QueueSessionController::class, 'status'])->name('queueSession.status');
    });

    /*
    | Admin Routes
    */
    Route::middleware(['role:Administrator,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            $today = Carbon::today();

            $totalQueueToday = \App\Models\QueueRequest::whereDate('requested_at', $today)->count();
            $waitingNow = \App\Models\QueueRequest::where('status', 'waiting')->count();
            $servingNow = \App\Models\QueueRequest::whereIn('status', ['called', 'serving'])->count();
            $completedToday = \App\Models\QueueRequest::where('status', 'completed')
                ->whereHas('transaction', function ($query) use ($today) {
                    $query->whereDate('completed_at', $today);
                })
                ->count();

            $stats = [
                'totalOffices' => \App\Models\Office::count(),
                'activeOffices' => \App\Models\Office::where('is_active', true)->count(),
                'totalServices' => \App\Models\Service::count(),
                'totalUsers' => \App\Models\User::count(),
                'totalQueueToday' => (int) $totalQueueToday,
                'waitingNow' => (int) $waitingNow,
                'servingNow' => (int) $servingNow,
                'completedToday' => (int) $completedToday,
            ];

            return Inertia::render('Admin/Index', ['stats' => $stats]);
        })->name('dashboard');

        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::resource('offices', OfficeController::class)->except(['create', 'edit', 'show']);
        Route::resource('services', ServiceController::class)->except(['create', 'edit', 'show']);
    });

});
