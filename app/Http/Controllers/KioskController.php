<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Inertia\Inertia;

class KioskController extends Controller
{
    public function index()
    {
        // Fetches offices along with their nested services
        $offices = Office::with('services')->get();

        return Inertia::render('Kiosk', [
            'offices' => $offices,
        ]);
    }
}