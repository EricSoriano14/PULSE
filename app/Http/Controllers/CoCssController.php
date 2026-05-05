<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CoCssController extends Controller
{
    public function index()
    {
        $coCssUsers = User::query()
            ->where('role', 'co_css')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $user->department = $this->normalizeDepartment($user->department);
                return $user;
            });

        return view('css.co-css', compact('coCssUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $name = trim($data['name']);
        $department = $this->normalizeDepartment($data['department'] ?? null);

        $user = User::create([
            'name' => $name,
            'full_name' => $name,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'department' => $department !== '' ? $department : null,
            'role' => 'co_css',
            'status' => 'active',
            'email_verified_at' => null,
        ]);

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Co-CSS account created. A verification email has been sent.');
    }

    public function update(Request $request, User $user)
    {
        if (strtolower((string) $user->role) !== 'co_css') {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'department' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $name = trim($data['name']);
        $department = $this->normalizeDepartment($data['department'] ?? null);

        $updateData = [
            'name' => $name,
            'full_name' => $name,
            'email' => $data['email'],
            'department' => $department !== '' ? $department : null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $emailChanged = $user->email !== $data['email'];

        if ($emailChanged) {
            $updateData['email_verified_at'] = null;
        }

        $user->update($updateData);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return back()->with('success', 'Co-CSS account updated. Email changed, so a new verification email has been sent.');
        }

        return back()->with('success', 'Co-CSS account updated.');
    }

    public function toggle(User $user)
    {
        if (strtolower((string) $user->role) !== 'co_css') {
            abort(404);
        }

        $currentStatus = strtolower((string) ($user->status ?? 'active'));
        $user->status = $currentStatus === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'Co-CSS account status updated.');
    }

    private function normalizeDepartment(?string $department): string
    {
        $department = strtoupper(trim((string) $department));

        if (in_array($department, ['CCS', 'COE'], true)) {
            return 'ECOAST';
        }

        return $department;
    }
}