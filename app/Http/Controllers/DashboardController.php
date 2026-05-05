<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower(trim((string) ($user->role ?? '')));
        $q = trim((string) $request->get('q', ''));

        $base = Report::query()
            ->with([
                'student',
                'assignedFaculty',
                'assignedCoCss',
                'reviewer',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%")
                        ->orWhere('calamity', 'like', "%{$q}%")
                        ->orWhere('department', 'like', "%{$q}%")
                        ->orWhere('status', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('student', function ($sq) use ($q) {
                            $sq->where('first_name', 'like', "%{$q}%")
                                ->orWhere('last_name', 'like', "%{$q}%")
                                ->orWhere('student_id_number', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        })
                        ->orWhereHas('user', function ($uq) use ($q) {
                            $uq->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            });

        if ($role === 'faculty') {
            $base->where('assigned_faculty_id', $user->id);
        } elseif ($role === 'co_css') {
            if (Schema::hasColumn('reports', 'assigned_co_css_id')) {
                $base->where('assigned_co_css_id', $user->id);
            } else {
                $base->whereRaw('1 = 0');
            }
        }

        $counts = [
            'reports' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'accepted' => (clone $base)->where('status', 'accepted')->count(),
            'declined' => (clone $base)->where('status', 'declined')->count(),
        ];

        $departmentStats = (clone $base)
            ->selectRaw("
                CASE
                    WHEN department IN ('CCS', 'COE') THEN 'ECOAST'
                    ELSE department
                END AS department_label,
                COUNT(*) as total
            ")
            ->whereNotNull('department')
            ->groupBy('department_label')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $row->department = $row->department_label;
                return $row;
            });

        $recentReports = (clone $base)
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($report) {
                $report->department = $this->normalizeDepartment($report->department);
                return $report;
            });

        return view('dashboard', compact(
            'counts',
            'departmentStats',
            'recentReports',
            'q'
        ));
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