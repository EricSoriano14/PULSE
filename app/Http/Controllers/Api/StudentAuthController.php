<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentEmailVerificationService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    private const ALLOWED_STUDENT_EMAIL_DOMAIN = '@panpacificu.edu.ph';

    public function login(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password'   => ['required', 'string'],
        ]);

        $identifier = trim($data['identifier']);

        $user = User::query()->where('email', $identifier)->first();

        if (!$user) {
            $user = User::query()->where('student_id', $identifier)->first();
        }

        if (!$user) {
            $student = Student::query()->where('student_id_number', $identifier)->first();
            if ($student && $student->user_id) {
                $user = User::find($student->user_id);
            }
        }

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Invalid credentials.'],
            ]);
        }

        $isStudent = strtolower((string) ($user->role ?? '')) === 'student'
            || !empty($user->student_id)
            || $user->student()->exists();

        if (!$isStudent) {
            throw ValidationException::withMessages([
                'identifier' => ['Account is not a student account.'],
            ]);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Your account has been disabled by the administrator.',
            ], 403);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message'        => 'Please verify your Panpacific email first before logging in.',
                'email_verified' => false,
                'email'          => $user->email,
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('student-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'                => $user->id,
                'full_name'         => $user->full_name ?? $user->name,
                'email'             => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    public function registrationOptions()
    {
        $departments = Department::query()
            ->with(['courses' => function ($query) {
                $query->orderBy('name');
            }])
            ->whereNotIn('name', ['CCS', 'COE'])
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) {
                return [
                    'id'      => $department->id,
                    'name'    => $department->name,
                    'courses' => $department->courses
                        ->map(function (Course $course) {
                            return [
                                'id'            => $course->id,
                                'department_id' => $course->department_id,
                                'name'          => $course->name,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'departments' => $departments,
            'years'       => [
                ['value' => 1, 'label' => '1st Year'],
                ['value' => 2, 'label' => '2nd Year'],
                ['value' => 3, 'label' => '3rd Year'],
                ['value' => 4, 'label' => '4th Year'],
            ],
        ]);
    }

    public function register(Request $request, StudentEmailVerificationService $verificationService)
    {
        $request->merge([
            'first_name'  => trim((string) $request->input('first_name')),
            'middle_name' => trim((string) $request->input('middle_name')),
            'last_name'   => trim((string) $request->input('last_name')),
            'student_id'  => trim((string) $request->input('student_id')),
            'email'       => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'middle_name'   => ['nullable', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'student_id'    => [
                'required', 'string', 'max:255',
                'unique:users,student_id',
                'unique:students,student_id_number',
            ],
            'email'         => [
                'required', 'email', 'max:255',
                'ends_with:' . self::ALLOWED_STUDENT_EMAIL_DOMAIN,
                'unique:users,email',
                'unique:students,email',
            ],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'course_id'     => ['required', 'integer', Rule::exists('courses', 'id')],
            'year'          => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'email.ends_with'   => 'Only Panpacific University email addresses are allowed.',
            'student_id.unique' => 'The student ID already exists.',
            'email.unique'      => 'The email already exists.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $department = Department::query()->whereKey($data['department_id'])->first();

        if (!$department) {
            throw ValidationException::withMessages([
                'department_id' => ['The selected department is invalid.'],
            ]);
        }

        $departmentName = $this->normalizeDepartment($department->name);

        $course = Course::query()
            ->whereKey($data['course_id'])
            ->where('department_id', $department->id)
            ->first();

        if (!$course) {
            throw ValidationException::withMessages([
                'course_id' => ['The selected course is invalid for the chosen department.'],
            ]);
        }

        $user = null;

        try {
            DB::transaction(function () use ($data, $departmentName, $course, &$user) {
                $fullName = $this->buildFullName(
                    $data['first_name'],
                    $data['middle_name'] ?? null,
                    $data['last_name']
                );

                $user = User::create([
                    'name'              => $fullName,
                    'full_name'         => $fullName,
                    'first_name'        => $data['first_name'],
                    'middle_name'       => $data['middle_name'] ?? null,
                    'last_name'         => $data['last_name'],
                    'student_id'        => $data['student_id'],
                    'email'             => $data['email'],
                    'course'            => $course->name,
                    'department'        => $departmentName,
                    'year'              => $data['year'],
                    'password'          => Hash::make($data['password']),
                    'role'              => 'student',
                    'status'            => 'active',
                    'email_verified_at' => null,
                    'safety_status'     => 'safe',
                ]);

                Student::create([
                    'user_id'           => $user->id,
                    'student_id_number' => $data['student_id'],
                    'first_name'        => $data['first_name'],
                    'last_name'         => $data['last_name'],
                    'email'             => $data['email'],
                    'year_level'        => (string) $data['year'],
                    'department_id'     => $data['department_id'],
                    'course_id'         => $course->id,
                    'status'            => 'active',
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $message = strtolower($e->getMessage());

            if (
                str_contains($message, 'users_student_id_unique') ||
                str_contains($message, 'students_student_id_number_unique')
            ) {
                throw ValidationException::withMessages([
                    'student_id' => ['The student ID already exists.'],
                ]);
            }

            if (
                str_contains($message, 'users_email_unique') ||
                str_contains($message, 'students_email_unique')
            ) {
                throw ValidationException::withMessages([
                    'email' => ['The email already exists.'],
                ]);
            }

            throw $e;
        }

        try {
            $verificationService->send($user);
        } catch (\Throwable $e) {
            \Log::error('Registration email failed: ' . $e->getMessage(), [
                'email' => $user->email,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message'    => 'Account created, but the OTP email could not be sent. Please use resend OTP.',
                'email_sent' => false,
                'email'      => $user->email,
            ], 201);
        }

        return response()->json([
            'message'    => 'Account created successfully. A 6-digit OTP has been sent to your Panpacific email. Enter it in the app to verify your account.',
            'email_sent' => true,
            'email'      => $user->email,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW: Verify Email via OTP
    |--------------------------------------------------------------------------
    */
    public function verifyEmailOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($data['email']));

        $record = DB::table('email_verification_otps')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'OTP not found or expired. Please request a new one.',
            ], 404);
        }

        $createdAt = Carbon::parse($record->created_at);

        if ($createdAt->addMinutes(10)->isPast()) {
            DB::table('email_verification_otps')->where('email', $email)->delete();

            return response()->json([
                'message' => 'OTP has expired. Please request a new one.',
            ], 400);
        }

        if (!Hash::check($data['otp'], $record->otp)) {
            return response()->json([
                'message' => 'Invalid OTP code.',
            ], 400);
        }

        // Mark email as verified
        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        if (is_null($user->email_verified_at)) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            event(new Verified($user));
        }

        // Clean up OTP
        DB::table('email_verification_otps')->where('email', $email)->delete();

        return response()->json([
            'message' => 'Email verified successfully. You may now log in.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NEW: Resend Email Verification OTP
    |--------------------------------------------------------------------------
    */
    public function resendVerificationOtp(Request $request, StudentEmailVerificationService $verificationService)
    {
        $data = $request->validate([
            'email' => [
                'required', 'email',
                'ends_with:' . self::ALLOWED_STUDENT_EMAIL_DOMAIN,
            ],
        ], [
            'email.ends_with' => 'Only Panpacific University email addresses are allowed.',
        ]);

        $email = strtolower(trim($data['email']));

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'student')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'No student account found for this email.',
            ], 404);
        }

        if (!is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'This email is already verified. You may now log in.',
            ]);
        }

        try {
            $verificationService->send($user);
        } catch (\Throwable $e) {
            \Log::error('Resend OTP email failed: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Failed to send OTP email. Please check mail configuration.',
            ], 500);
        }

        return response()->json([
            'message' => 'A new OTP has been sent to your email.',
        ]);
    }

    public function me(Request $request)
    {
        $user    = $request->user();
        $student = $user->student()->with(['department', 'course'])->first();

        return response()->json([
            'user' => [
                'id'                => $user->id,
                'full_name'         => $user->full_name ?? $user->name,
                'email'             => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
            'student' => $student ? [
                'id'                => $student->id,
                'student_id_number' => $student->student_id_number,
                'first_name'        => $student->first_name,
                'last_name'         => $student->last_name,
                'year_level'        => $student->year_level,
                'department'        => $student->department?->name,
                'course'            => $student->course?->name,
                'status'            => $student->status,
            ] : null,
        ]);
    }

    public function sendPasswordOtp(Request $request)
    {
        $data  = $request->validate(['email' => ['required', 'email']]);
        $email = trim($data['email']);
        $user  = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'No account found for this email.'], 404);
        }

        $isStudent = strtolower((string) ($user->role ?? '')) === 'student'
            || !empty($user->student_id)
            || $user->student()->exists();

        if (!$isStudent) {
            return response()->json(['error' => 'This account is not a student account.'], 403);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => Hash::make($otp),
            'created_at' => now(),
        ]);

        try {
            Mail::raw(
                "Your OTP code for password reset is: {$otp}\n\nThis code will expire in 10 minutes.\n\nIf you did not request this, please ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)->subject('Student Account Password Reset OTP');
                }
            );

            return response()->json(['message' => 'OTP sent successfully to your email.']);
        } catch (\Exception $e) {
            \Log::error('Password OTP email failed: ' . $e->getMessage(), [
                'email' => $email,
                'trace' => $e->getTraceAsString(),
            ]);
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json(['error' => 'Failed to send OTP email. Please check mail configuration.'], 500);
        }
    }

    public function verifyPasswordOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$record) {
            return response()->json(['error' => 'OTP not found or expired.'], 404);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return response()->json(['error' => 'OTP has expired.'], 400);
        }

        if (!Hash::check($data['otp'], $record->token)) {
            return response()->json(['error' => 'Invalid OTP code.'], 400);
        }

        return response()->json(['message' => 'OTP verified successfully.']);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email'        => ['required', 'email'],
            'otp'          => ['required', 'digits:6'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'No account found for this email.'], 404);
        }

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (!$record) {
            return response()->json(['error' => 'OTP not found or expired.'], 404);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return response()->json(['error' => 'OTP has expired.'], 400);
        }

        if (!Hash::check($data['otp'], $record->token)) {
            return response()->json(['error' => 'Invalid OTP code.'], 400);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
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
}