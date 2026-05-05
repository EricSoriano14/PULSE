<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// ✅ Phase 4 seeders (make sure these files exist in database/seeders)
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\StudentBackfillSeeder;
use Database\Seeders\ReportStudentBackfillSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ================================
        // PHASE 3 DEMO DATA (KEEP)
        // ================================
        // Define department-specific courses matching the controller
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

        $departments = array_keys($departmentCourses);

        // Clear existing student data only (preserve admin accounts)
        // Delete reports first (they depend on users)
        Report::query()->delete();

        // Only delete users with student_id (students), keep admin accounts
        User::whereNotNull('student_id')->delete();

        foreach ($departments as $dept) {
            $courses = $departmentCourses[$dept];
            $courseCount = count($courses);

            // Ensure departments have enough students:
            // - More courses = fewer students per course (but still substantial)
            // - Fewer courses = more students per course to have enough content
            if ($courseCount >= 5) {
                $studentsPerCourse = 4; // Departments with many courses
            } elseif ($courseCount >= 3) {
                $studentsPerCourse = 5; // Medium departments
            } else {
                $studentsPerCourse = 8; // Departments with few courses (PUMMA, SOC, CBHIS)
            }

            $studentCounter = 1;

            // Create students for each course in the department
            foreach ($courses as $courseIndex => $course) {
                for ($i = 0; $i < $studentsPerCourse; $i++) {
                    $studentId = $dept . '-' . str_pad((string) $studentCounter, 3, '0', STR_PAD_LEFT);
                    $email = strtolower($dept) . $studentCounter . '@student.example.com';

                    // Create user
                    $user = User::create([
                        'name' => fake()->name(),
                        'student_id' => $studentId,
                        'course' => $course,
                        'department' => $dept,
                        'email' => $email,
                        'password' => bcrypt('password'),
                    ]);

                    // Create 2-5 reports per student for more content
                    $reportCount = fake()->numberBetween(2, 5);
                    for ($r = 1; $r <= $reportCount; $r++) {
                        Report::create([
                            'user_id' => $user->id,
                            'calamity' => fake()->randomElement(['Flood', 'Fire', 'Earthquake', 'Typhoon', 'Landslide', 'Drought', 'Storm Surge']),
                            'department' => $dept,
                            'description' => fake()->sentence(10),

                            // ✅ LOCKED RULE: do NOT use "reviewed"
                            // Keep only allowed statuses
                            'status' => fake()->randomElement(['pending', 'accepted', 'declined', 'action_taken']),

                            'submitted_at' => now()
                                ->subDays(fake()->numberBetween(0, 60))
                                ->subMinutes(fake()->numberBetween(0, 1440)),
                        ]);
                    }

                    $studentCounter++;
                }
            }
        }

        // ================================
        // PHASE 4 FINAL SCHEMA BACKFILL
        // (departments/courses/students + link reports.student_id)
        // ================================
        $this->call([
            DepartmentSeeder::class,
            CourseSeeder::class,
            StudentBackfillSeeder::class,
            ReportStudentBackfillSeeder::class,
        ]);
    }
}
