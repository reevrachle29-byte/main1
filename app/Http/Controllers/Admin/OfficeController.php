<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\AuditLog;

class OfficeController extends Controller
{
    /**
     * Display a listing of campus offices and available staff.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Offices/Index', [
            // Select user_id instead of id in relationship eager loading
            'offices' => Office::with('user:user_id,name,email')
                ->latest()
                ->get(),

            // Select user_id instead of id for staff assignment options
            'staffUsers' => User::whereIn('role', ['staff', 'employee'])
                ->select('user_id', 'name', 'email')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created office in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:offices,name',
            'user_id'   => 'nullable|exists:users,user_id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['user_id'])) {
            abort_unless(User::where('user_id', $validated['user_id'])->whereIn('role', ['staff', 'employee'])->exists(), 422, 'Only staff can be assigned to an office.');
        }

        Office::create($validated);
        AuditLog::log('admin_office_created', 'Created office: ' . $validated['name'], ['user_id' => $validated['user_id'] ?? null]);

        return redirect()->back()->with('success', 'Office created successfully.');
    }

    /**
     * Update the specified office in storage.
     */
    public function update(Request $request, Office $office): RedirectResponse
    {
        // Dynamically get primary key name for Office ($office->getKey())
        $officeKey = $office->getKey();

        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:offices,name,' . $officeKey . ',' . $office->getKeyName(),
            'user_id'   => 'nullable|exists:users,user_id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['user_id'])) {
            abort_unless(User::where('user_id', $validated['user_id'])->whereIn('role', ['staff', 'employee'])->exists(), 422, 'Only staff can be assigned to an office.');
        }

        $office->update($validated);
        AuditLog::log('admin_office_updated', 'Updated office #' . $office->office_id, $validated);

        return redirect()->back()->with('success', 'Office updated successfully.');
    }

    /**
     * Remove the specified office from storage.
     */
    public function destroy(Office $office): RedirectResponse
    {
        $office->update(['is_active' => false]);
        AuditLog::log('admin_office_deactivated', 'Deactivated office #' . $office->office_id);

        return redirect()->back()->with('success', 'Office deactivated. Historical queue data was preserved.');
    }
}