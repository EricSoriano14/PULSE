<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Student\StoreStudentReportRequest;
use App\Http\Resources\Api\Student\StudentReportResource;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentReportController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function departments()
    {
        $departments = Department::query()
            ->whereNotIn('name', ['CCS', 'COE'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($departments);
    }

    public function faculty(Request $request)
    {
        $department = $this->normalizeDepartment($request->query('department'));

        $query = User::query()
            ->where('role', 'faculty');

        if (!empty($department)) {
            $query->whereIn('department', $this->departmentAliases($department));
        }

        if ($this->userHasColumn('status')) {
            $query->where('status', 'active');
        }

        $faculty = $query
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department'])
            ->map(function ($item) {
                $item->department = $this->normalizeDepartment($item->department);
                return $item;
            })
            ->values();

        return response()->json($faculty);
    }

    public function index(Request $request)
    {
        $student = $this->resolveStudent($request);

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 50));

        $reports = Report::query()
            ->where('student_id', $student->id)
            ->latest()
            ->with([
                'images:id,report_id,path,image_url,created_at',
                'latestStudentVisibleAction:id,report_id,recommended_action,recommended_note,recommended_at,public_remark,action_taken_note,created_at',
                'assignedFaculty:id,name,email,department',
            ])
            ->paginate($perPage);

        return StudentReportResource::collection($reports);
    }

    public function show(Request $request, Report $report)
    {
        $student = $this->resolveStudent($request);

        abort_unless((int) $report->student_id === (int) $student->id, 404);

        $report->load([
            'images:id,report_id,path,image_url,created_at',
            'latestStudentVisibleAction:id,report_id,recommended_action,recommended_note,recommended_at,public_remark,action_taken_note,created_at',
            'assignedFaculty:id,name,email,department',
        ]);

        return new StudentReportResource($report);
    }

    public function store(StoreStudentReportRequest $request)
    {
        $student = $this->resolveStudent($request);
        $authUser = $request->user();

        $department = $this->normalizeDepartment($request->input('department'));
        $assignedFacultyId = (int) $request->input('assigned_faculty_id');

        $facultyQuery = User::query()
            ->whereKey($assignedFacultyId)
            ->where('role', 'faculty')
            ->whereIn('department', $this->departmentAliases($department));

        if ($this->userHasColumn('status')) {
            $facultyQuery->where('status', 'active');
        }

        $assignedFaculty = $facultyQuery->first();

        if (!$assignedFaculty) {
            return response()->json([
                'message' => 'The selected instructor is invalid for the chosen department.'
            ], 422);
        }

        $report = new Report();

        $report->student_id = $student->id;

        if ($this->hasColumnOrFillable($report, 'user_id')) {
            $report->user_id = $authUser?->id;
        }

        if ($this->hasColumnOrFillable($report, 'department')) {
            $report->department = $department;
        }

        if ($this->hasColumnOrFillable($report, 'assigned_faculty_id')) {
            $report->assigned_faculty_id = $assignedFaculty->id;
        }

        $report->status = 'pending';

        if ($this->hasColumnOrFillable($report, 'submitted_at')) {
            $report->submitted_at = now();
        }

        $report->title = $request->input('title');
        $report->category = $request->input('category');
        $report->description = $request->input('description');
        $report->calamity = $request->input('calamity_type');

        if ($this->hasColumnOrFillable($report, 'latitude')) {
            $report->latitude = $request->input('latitude');
        }

        if ($this->hasColumnOrFillable($report, 'longitude')) {
            $report->longitude = $request->input('longitude');
        }

        if ($this->hasColumnOrFillable($report, 'location_address')) {
            $report->location_address = $request->input('location_address');
        }

        if (
            $request->filled('incident_datetime') &&
            $this->hasColumnOrFillable($report, 'incident_datetime')
        ) {
            $report->incident_datetime = $request->input('incident_datetime');
        }

        $report->save();

        $files = [];

        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }

        if ($request->hasFile('images')) {
            foreach ((array) $request->file('images') as $f) {
                if ($f) {
                    $files[] = $f;
                }
            }
        }

        foreach ($files as $file) {
            $path = $file->store('reports', 'public');
            $relativeUrl = Storage::disk('public')->url($path);

            $report->images()->create([
                'path' => $path,
                'image_url' => $relativeUrl,
            ]);
        }

        $category = strtolower(trim((string) $request->input('category', '')));
        $title    = strtolower(trim((string) $request->input('title', '')));
        $calamity = strtolower(trim((string) $request->input('calamity_type', '')));

        $notSafeKeywords = [
            "i'm not safe",
            "im not safe",
            "not safe",
            "unsafe",
            "at_risk",
            "at risk",
        ];

        $isNotSafe =
            in_array($category, $notSafeKeywords, true) ||
            in_array($title, $notSafeKeywords, true) ||
            in_array($calamity, $notSafeKeywords, true);

        if ($isNotSafe && $authUser) {
            $canSetSafety =
                (method_exists($authUser, 'getAttributes') && array_key_exists('safety_status', $authUser->getAttributes())) ||
                (method_exists($authUser, 'isFillable') && $authUser->isFillable('safety_status'));

            if ($canSetSafety) {
                $authUser->safety_status = 'at_risk';
                $authUser->save();
            }
        }

        $this->notifyStaffAboutNewReport($report, $authUser, $assignedFaculty);

        $report->refresh();

        $report->load([
            'images:id,report_id,path,image_url,created_at',
            'latestStudentVisibleAction:id,report_id,recommended_action,recommended_note,recommended_at,public_remark,action_taken_note,created_at',
            'assignedFaculty:id,name,email,department',
        ]);

        return (new StudentReportResource($report))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Report $report)
    {
        $student = $this->resolveStudent($request);

        if ((int) $report->student_id !== (int) $student->id) {
            abort(404);
        }

        if ($report->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending reports can be deleted.'
            ], 403);
        }

        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully.'
        ]);
    }

    private function resolveStudent(Request $request)
    {
        $auth = $request->user();

        if ($auth instanceof Student) {
            return $auth;
        }

        if ($auth && method_exists($auth, 'student') && $auth->student) {
            return $auth->student;
        }

        abort(403, 'Student context not found for this token.');
    }

    private function hasColumnOrFillable(Report $report, string $key): bool
    {
        return array_key_exists($key, $report->getAttributes()) || $report->isFillable($key);
    }

    private function userHasColumn(string $key): bool
    {
        $user = new User();

        return array_key_exists($key, $user->getAttributes()) || $user->isFillable($key);
    }

    private function notifyStaffAboutNewReport(Report $report, ?User $actor, ?User $assignedFaculty): void
    {
        $cssUsers = User::query()
            ->where('role', 'css')
            ->when($this->userHasColumn('status'), function ($query) {
                $query->where('status', 'active');
            })
            ->get();

        $recipients = $cssUsers;

        if ($assignedFaculty) {
            $alreadyIncluded = $recipients->contains(fn ($user) => (int) $user->id === (int) $assignedFaculty->id);

            if (!$alreadyIncluded) {
                $recipients->push($assignedFaculty);
            }
        }

        if ($recipients->isEmpty()) {
            return;
        }

        $studentName = $actor?->name ?: 'A student';
        $reportTitle = trim((string) $report->title) !== '' ? $report->title : 'Untitled Report';

        $this->notificationService->createForUsers(
            $recipients,
            Notification::TYPE_STAFF_NEW_REPORT,
            'New Student Report',
            "{$studentName} submitted a report: {$reportTitle}.",
            $report,
            null,
            $actor,
            route('receive-report.show', $report, false)
        );
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