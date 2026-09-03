<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('office:office_id,name')
            ->orderBy('service_id', 'desc')
            ->get();

        $offices = Office::where('is_active', true)
            ->select('office_id', 'name')
            ->get();

        return Inertia::render('Admin/Services/Index', [
            'services' => $services,
            'offices' => $offices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,office_id',
            'service_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Service::create($validated);

        return redirect()->back()->with('success', 'Service created successfully.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,office_id',
            'service_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);

        return redirect()->back()->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->back()->with('success', 'Service deleted successfully.');
    }
}