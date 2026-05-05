<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentEmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ManageController extends Controller
{
    private const ALLOWED_STUDENT_EMAIL_DOMAIN = '@panpacificu.edu.ph';

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $department = $this->normalizeDepartment($request->get('department'));
        $course = trim((string) $request->get('course', ''));
        $year = trim((string) $request->get('year', ''));

        $users = User::query()
            ->where('role', 'student')
            ->when($department !== '', function ($query) use ($department) {
                $query->whereIn('department', $this->departmentAliases($department));
            })
            ->when($course !== '', fn ($query) => $query->where('course', $course))
            ->when($year !== '', fn ($query) => $query->where('year', $year))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('student_id', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $departmentModels = Department::with(['courses' => function ($query) {
            $query->orderBy('name');
        }])
            ->whereNotIn('name', ['CCS', 'COE'])
            ->orderBy('name')
            ->get();

        $departmentCourses = $departmentModels->mapWithKeys(function ($departmentModel) {
            return [
                $departmentModel->name => $departmentModel->courses
                    ->pluck('name')
                    ->values()
                    ->all(),
            ];
        });

        $allDepartments = $departmentModels->pluck('name')->values();

        return view('manage', compact('users', 'departmentCourses', 'allDepartments'));
    }

    public function store(Request $request, StudentEmailVerificationService $verificationService)
    {
        $request->merge([
            'first_name' => trim((string) $request->input('first_name')),
            'middle_name' => trim((string) $request->input('middle_name')),
            'last_name' => trim((string) $request->input('last_name')),
            'student_id' => trim((string) $request->input('student_id')),
            'email' => strtolower(trim((string) $request->input('email'))),
            'course' => trim((string) $request->input('course')),
            'department' => trim((string) $request->input('department')),
        ]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],

            'student_id' => [
                'required',
                'string',
                'max:255',
                'unique:users,student_id',
                'unique:students,student_id_number',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'ends_with:' . self::ALLOWED_STUDENT_EMAIL_DOMAIN,
                'unique:users,email',
                'unique:students,email',
            ],

            'course' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'student_id.unique' => 'The student ID already exists.',
            'email.unique' => 'The email already exists.',
            'email.ends_with' => 'Only Panpacific University email addresses are allowed.',
        ]);

        $data['department'] = $this->normalizeDepartment($data['department']);

        $dept = Department::where('name', trim($data['department']))->first();

        if (!$dept) {
            throw ValidationException::withMessages([
                'department' => 'The selected department is invalid.',
            ]);
        }

        $course = Course::where('department_id', $dept->id)
            ->where('name', trim($data['course']))
            ->first();

        if (!$course) {
            throw ValidationException::withMessages([
                'course' => 'The selected course is invalid for the chosen department.',
            ]);
        }

        $user = null;

        try {
            DB::transaction(function () use ($data, $dept, $course, &$user) {
                $fullName = $this->buildFullName(
                    $data['first_name'],
                    $data['middle_name'] ?? null,
                    $data['last_name']
                );

                $user = User::create([
                    'name' => $fullName,
                    'full_name' => $fullName,
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'student_id' => $data['student_id'],
                    'email' => $data['email'],
                    'course' => $course->name,
                    'department' => $data['department'],
                    'year' => $data['year'],
                    'password' => Hash::make('student123'),
                    'role' => 'student',
                    'status' => 'active',

                    /*
                    |--------------------------------------------------------------------------
                    | Option 2 Final Rule
                    |--------------------------------------------------------------------------
                    | Admin-created students must also verify email before login.
                    */
                    'email_verified_at' => null,

                    'safety_status' => 'safe',
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'student_id_number' => $data['student_id'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'year_level' => (string) $data['year'],
                    'department_id' => $dept->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strtolower($e->getMessage());

            if (
                str_contains($message, 'users_student_id_unique') ||
                str_contains($message, 'students_student_id_number_unique')
            ) {
                throw ValidationException::withMessages([
                    'student_id' => 'The student ID already exists.',
                ]);
            }

            if (
                str_contains($message, 'users_email_unique') ||
                str_contains($message, 'students_email_unique')
            ) {
                throw ValidationException::withMessages([
                    'email' => 'The email already exists.',
                ]);
            }

            throw $e;
        }

        try {
            $verificationService->send($user);
        } catch (\Throwable $e) {
            return redirect()
                ->route('manage')
                ->with('success', 'Student added successfully, but the verification email could not be sent. Please check mail configuration.');
        }

        return redirect()
            ->route('manage')
            ->with('success', 'Student added successfully. A verification email was sent. Default password is student123.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'Student removed.');
    }

    private function buildFullName(string $firstName, ?string $middleName, string $lastName): string
    {
        return trim(collect([
            trim($firstName),
            trim((string) $middleName),
            trim($lastName),
        ])->filter()->implode(' '));
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