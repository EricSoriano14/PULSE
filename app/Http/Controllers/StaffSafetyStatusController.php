<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffSafetyStatusController extends Controller
{
    public function index(Request $request)
    {
        // Admin-only (extra guard)
        $viewerRole = strtolower(trim((string) (auth()->user()?->role ?? '')));
        abort_unless($viewerRole === 'admin', 403);

        $staff = User::query()
            ->whereIn('role', ['css', 'faculty'])
            ->orderBy('role')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'role', 'safety_status', 'updated_at']);

        return view('staff-safety-status', compact('staff'));
    }

    public function update(Request $request, User $user)
    {
        // Admin-only (extra guard)
        $viewerRole = strtolower(trim((string) (auth()->user()?->role ?? '')));
        abort_unless($viewerRole === 'admin', 403);

        // Only allow updating staff accounts
        $targetRole = strtolower(trim((string) ($user->role ?? '')));
        abort_unless(in_array($targetRole, ['css', 'faculty'], true), 403);

        $validated = $request->validate([
            'safety_status' => ['required', 'in:safe,at_risk'],
        ]);

        $user->safety_status = $validated['safety_status'];
        $user->save();

        // ✅ redirect to correct named route
        return redirect()
            ->route('staff-safety-status.index')
            ->with('success', 'Staff safety status updated successfully.');
    }
}
