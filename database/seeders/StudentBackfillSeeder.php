<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Support\Str;

class StudentBackfillSeeder extends Seeder
{
    public function run(): void
    {
        // Heuristic: "student" users = has student_id OR email looks like student
        $users = User::query()
            ->whereNotNull('student_id')
            ->orWhere('email', 'like', '%student%')
            ->get();

        foreach ($users as $user) {
            $deptName = trim((string)($user->department ?? ''));
            $courseName = trim((string)($user->course ?? ''));

            // Department
            $department = null;
            if ($deptName !== '') {
                $department = Department::firstOrCreate(['name' => $deptName]);
            } else {
                $department = Department::firstOrCreate(['name' => 'UNASSIGNED']);
            }

            // Course (must belong to department)
            $course = null;
            if ($courseName !== '') {
                $course = Course::firstOrCreate([
                    'department_id' => $department->id,
                    'name' => $courseName,
                ]);
            } else {
                $course = Course::firstOrCreate([
                    'department_id' => $department->id,
                    'name' => 'UNASSIGNED',
                ]);
            }

            // Name fallback: if no first/last in users, parse "name"
            $firstName = $user->first_name ?: (Str::of((string)$user->name)->explode(' ')->first() ?? 'Student');
            $lastName = $user->last_name ?: (Str::of((string)$user->name)->explode(' ')->last() ?? 'User');

            Student::firstOrCreate(
                ['email' => $user->email],
                [
                    'user_id' => $user->id,
                    'student_id_number' => $user->student_id ?? ('STU-' . $user->id),
                    'first_name' => (string)$firstName,
                    'last_name' => (string)$lastName,
                    'year_level' => $user->year ?? '1',
                    'department_id' => $department->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                ]
            );
        }
    }
}
