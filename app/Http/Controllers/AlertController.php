<?php

namespace App\Http\Controllers;

use App\Models\Alert;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::with('product')
            ->where('is_resolved', false)
            ->latest()
            ->get();

        return view('dashboard.alerts.index', compact('alerts'));
    }

    public function resolve($id)
    {
        $alert = Alert::findOrFail($id);

        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now()
        ]);

        return redirect()->route('alerts.index')
            ->with('success', 'Alert resolved');
    }
}
