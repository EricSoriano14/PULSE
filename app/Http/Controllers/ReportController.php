<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $department = $this->normalizeDepartment($request->query('department'));
        $course = trim((string) $request->query('course', ''));

        $departments = collect([
            'ECOAST',
            'PBS',
            'PUMMA',
            'RPSEA',
            'CBHIS',
            'SOC',
        ]);

        $departmentCourses = [
            'ECOAST' => [
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Computer Science',
                'Bachelor of Science in Computer Engineering',
                'Bachelor of Science in Electrical Engineering',
                'Bachelor of Science in Electronics Engineering',
                'Bachelor of Science in Civil Engineering',
            ],
            'PBS' => [
                'Bachelor of Science in Accountancy',
                'Bachelor of Science in Business Administration',
                'Marketing Management',
                'Financial Management',
                'Bachelor of Science in Hospitality Management',
                'Bachelor of Science in Tourism Management',
            ],
            'PUMMA' => [
                'Bachelor of Science in Marine Transportation',
            ],
            'RPSEA' => [
                'Bachelor of Arts in Psychology',
                'Bachelor of Elementary Education',
                'Bachelor of Secondary Education (majors in Mathematics, English, Social Sciences, Filipino)',
                'Bachelor of Physical Education',
                'Bachelor of Public Administration',
            ],
            'CBHIS' => [
                'Bachelor of Science in Nursing',
                'Bachelor of Science in Pharmacy',
            ],
            'SOC' => [
                'Bachelor of Science in Criminology',
            ],
        ];

        $user = Auth::user();
        $role = strtolower(trim((string) ($user?->role ?? '')));

        $reports = Report::query()
            ->with([
                'user',
                'student.department',
                'student.course',
                'assignedFaculty',
                'assignedCoCss',
            ])
            ->where('status', 'pending')
            ->when($role === 'faculty', function ($q) use ($user) {
                $q->where('assigned_faculty_id', $user->id);
            })
            ->when($role === 'co_css', function ($q) use ($user) {
                if (Schema::hasColumn('reports', 'assigned_co_css_id')) {
                    $q->where('assigned_co_css_id', $user->id);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when($department !== '', function ($q) use ($department) {
                $aliases = $this->departmentAliases($department);

                return $q->where(function ($query) use ($aliases) {
                    $query->whereIn('department', $aliases)
                        ->orWhereHas('user', function ($userQuery) use ($aliases) {
                            $userQuery->whereIn('department', $aliases);
                        })
                        ->orWhereHas('student.department', function ($deptQuery) use ($aliases) {
                            $deptQuery->whereIn('name', $aliases);
                        });
                });
            })
            ->when($course !== '', function ($q) use ($course) {
                return $q->where(function ($query) use ($course) {
                    $query->whereHas('user', function ($userQuery) use ($course) {
                        $userQuery->where('course', $course);
                    })
                    ->orWhereHas('student.course', function ($courseQuery) use ($course) {
                        $courseQuery->where('name', $course);
                    });
                });
            })
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('receive-report', compact('reports', 'departments', 'department', 'course', 'departmentCourses'));
    }

    public function show(Report $report)
    {
        $report->load([
            'user',
            'student.department',
            'student.course',
            'images',
            'action',
            'assignedFaculty',
            'assignedCoCss',
        ]);

        $user = Auth::user();
        $role = strtolower(trim((string) ($user?->role ?? '')));

        if ($role === 'faculty' && (int) $report->assigned_faculty_id !== (int) $user->id) {
            abort(403);
        }

        if ($role === 'co_css') {
            if (!Schema::hasColumn('reports', 'assigned_co_css_id')) {
                abort(403);
            }

            if ((int) $report->assigned_co_css_id !== (int) $user->id) {
                abort(403);
            }
        }

        $coCssUsers = in_array($role, ['admin', 'css'], true)
            ? User::query()
                ->where('role', 'co_css')
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
            : collect();

        return view('receive-report-show', compact('report', 'coCssUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'calamity' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Report::create([
            'user_id' => Auth::id(),
            'calamity' => $data['calamity'],
            'department' => $this->normalizeDepartment($data['department'] ?? Auth::user()?->department),
            'description' => $data['description'] ?? null,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return redirect()->route('receive-report')->with('success', 'Report submitted.');
    }

    public function updateStatus(Request $request, Report $report)
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? '')));
        if (!in_array($role, ['admin', 'css', 'faculty', 'co_css'], true)) {
            abort(403);
        }

        if ($role === 'faculty' && (int) $report->assigned_faculty_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($role === 'co_css') {
            if (!Schema::hasColumn('reports', 'assigned_co_css_id')) {
                abort(403);
            }

            if ((int) $report->assigned_co_css_id !== (int) Auth::id()) {
                abort(403);
            }
        }

        $data = $request->validate([
            'status' => ['required', 'in:accepted,declined'],
        ]);

        $to = $data['status'];
        $from = $report->status;

        $allowed = match ($from) {
            'pending' => in_array($to, ['accepted', 'declined'], true),
            default => false,
        };

        if (!$allowed) {
            return back()->with('error', "Invalid transition: {$from} → {$to}");
        }

        $report->status = $to;
        $report->reviewed_at = now();
        $report->reviewed_by = Auth::id();
        $report->save();

        return back()->with('success', "Report updated: {$from} → {$to}");
    }

    public function assignCoCss(Request $request, Report $report)
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? '')));

        if (!in_array($role, ['admin', 'css'], true)) {
            abort(403);
        }

        if (!Schema::hasColumn('reports', 'assigned_co_css_id')) {
            return back()->with('error', 'Co-CSS assignment column is not available yet. Please run migrations first.');
        }

        if ($report->status !== 'pending') {
            return back()->with('error', 'Co-CSS can only be assigned while the report is still pending.');
        }

        $data = $request->validate([
            'co_css_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $coCssUser = User::query()
            ->where('id', $data['co_css_id'])
            ->where('role', 'co_css')
            ->where('status', 'active')
            ->first();

        if (!$coCssUser) {
            return back()->with('error', 'Selected user is not a valid active Co-CSS.');
        }

        $alreadyAssignedToSameUser = (int) $report->assigned_co_css_id === (int) $coCssUser->id;

        $report->assigned_co_css_id = $coCssUser->id;
        $report->save();

        if (!$alreadyAssignedToSameUser) {
            $actor = Auth::user();
            $reportTitle = trim((string) $report->title) !== '' ? $report->title : 'Untitled Report';

            $this->notificationService->createForUser(
                $coCssUser,
                Notification::TYPE_STAFF_CO_CSS_ASSIGNMENT,
                'Report Assigned for Review',
                "CSS assigned you to review the report: {$reportTitle}.",
                $report,
                null,
                $actor,
                route('receive-report.show', $report, false)
            );
        }

        return back()->with('success', 'Co-CSS assigned successfully.');
    }

    private function normalizeDepartment(?string $department): string
    {
        $department = strtoupper(trim((string) $department));

        if (in_array($department, ['CCS', 'COE'], true)) {
            return 'ECOAST';
        }

        return $department;
    }

    private function departmentAliases(string $department): array
    {
        if ($department === 'ECOAST') {
            return ['ECOAST', 'CCS', 'COE'];
        }

        return [$department];
    }
}