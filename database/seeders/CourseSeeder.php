<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Department;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Minimal working courses. We'll expand later if needed.
        $map = [
            'ECOAST' => [
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Computer Science',
            ],
            'CCS' => [
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Computer Science',
            ],
            'COE' => [
                'Bachelor of Science in Civil Engineering',
            ],
        ];

        foreach ($map as $deptName => $courses) {
            $dept = Department::firstOrCreate(['name' => $deptName]);

            foreach ($courses as $courseName) {
                Course::firstOrCreate([
                    'department_id' => $dept->id,
                    'name' => $courseName,
                ]);
            }
        }
    }
}
