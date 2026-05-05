<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminStaffController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $role = trim((string) $request->get('role', ''));

        $staffUsers = User::query()
            ->whereIn('role', ['faculty', 'css', 'co_css'])
            ->when($role !== '', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('full_name', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('department', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.staff.index', compact('staffUsers'));
    }

    public function create()
    {
        $departments = [
            'ECOAST',
            'PBS',
            'PUMMA',
            'RPSEA',
            'CBHIS',
            'SOC',
        ];

        return view('admin.staff.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:faculty,css,co_css'],
            'department' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $department = $this->normalizeDepartment($data['department'] ?? null);

        if ($data['role'] === 'faculty' && empty($department)) {
            return back()
                ->withErrors(['department' => 'Department is required for faculty accounts.'])
                ->withInput();
        }

        $user = User::create([
            'full_name' => $data['full_name'],
            'name' => $data['full_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'department' => !empty($department) ? $department : null,
            'status' => 'active',
            'email_verified_at' => null,
            'password' => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', ucfirst(str_replace('_', '-', $data['role'])) . ' account created successfully. A verification email has been sent.');
    }

    public function toggle(User $user)
    {
        if (!in_array($user->role, ['faculty', 'css', 'co_css'], true)) {
            abort(404);
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account status updated successfully.');
    }

    private function normalizeDepartment(?string $department): ?string
    {
        $department = strtoupper(trim((string) $department));

        if ($department === '' || $department === 'NULL') {
            return null;
        }

        if (in_array($department, ['CCS', 'COE'], true)) {
            return 'ECOAST';
        }

        return $department;
    }
}