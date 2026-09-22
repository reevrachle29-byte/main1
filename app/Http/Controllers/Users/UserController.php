<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\AuditLog;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();

        return Inertia::render('User/Dashboard', [
            'users' => User::with('office:office_id,name')
                ->orderBy('user_id', 'desc')
                ->get(),
            'userStats' => [
                'today' => User::where('created_at', '>=', $now->copy()->startOfDay())->count(),
                'week' => User::where('created_at', '>=', $now->copy()->startOfWeek())->count(),
                'month' => User::where('created_at', '>=', $now->copy()->startOfMonth())->count(),
                'year' => User::where('created_at', '>=', $now->copy()->startOfYear())->count(),
                'total' => User::count(),
            ],
            'offices' => Office::select('office_id', 'name', 'is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,staff,student,administrator,employee',
            'contact' => 'nullable|string|max:20',
            'office_id' => 'nullable|exists:offices,office_id',
        ]);

        if (!in_array(strtolower($validated['role']), ['staff', 'employee'], true)) {
            $validated['office_id'] = null;
        }

        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);

        User::create($validated);
        AuditLog::log('admin_user_created', 'Created user: ' . $validated['email'], ['role' => $validated['role']]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'role' => 'required|string|in:admin,staff,student,administrator,employee',
            'contact' => 'nullable|string|max:20',
            'office_id' => 'nullable|exists:offices,office_id',
        ]);

        if (!in_array(strtolower($validated['role']), ['staff', 'employee'], true)) {
            $validated['office_id'] = null;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        AuditLog::log('admin_user_updated', 'Updated user #' . $user->user_id, ['role' => $validated['role'], 'office_id' => $validated['office_id'] ?? null]);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ((int) $user->user_id === (int) Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own administrator account.');
        }

        if ($user->isAdmin() && User::whereIn('role', ['admin', 'administrator'])->count() <= 1) {
            return redirect()->back()->with('error', 'The last administrator cannot be deleted.');
        }

        if ($user->queueRequests()->whereIn('status', ['waiting', 'called', 'serving'])->exists()) {
            return redirect()->back()->with('error', 'Users with active queue tickets cannot be deleted.');
        }

        $user->delete();
        AuditLog::log('admin_user_deleted', 'Deleted user #' . $user->user_id, ['email' => $user->email]);

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}