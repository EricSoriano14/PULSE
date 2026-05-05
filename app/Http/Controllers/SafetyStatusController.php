<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SafetyStatusController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('safety-status', compact('user'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'safety_status' => ['required', 'in:safe,at_risk'],
        ]);

        $user = Auth::user();
        $user->safety_status = $data['safety_status'];
        $user->save();

        return back()->with('success', 'Your safety status has been updated.');
    }
}
