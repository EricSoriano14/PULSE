<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class StudentsStatusController extends Controller
{
    public function index(Request $request)
    {
        $department = trim((string) $request->query('department', ''));
        $courseParam = trim((string) $request->query('course', ''));
        $searchId = trim((string) $request->query('search_id', ''));
        
        // If course is empty string from query, explicitly set to null/empty
        $course = ($courseParam === '') ? null : $courseParam;

        // Keep the filter options consistent even if there are no records yet.
        $departments = collect(['ECOAST', 'PBS', 'PUMMA', 'RPSEA', 'CBHIS', 'SOC']);

        // Define department-specific courses
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

        // Get all courses (will be filtered by JavaScript based on department)
        $allCourses = [];
        foreach ($departmentCourses as $dept => $deptCourses) {
            foreach ($deptCourses as $courseName) {
                if (!in_array($courseName, $allCourses)) {
                    $allCourses[] = $courseName;
                }
            }
        }
        // Also get courses from database for departments not yet defined
        $dbCourses = User::query()
            ->whereNotNull('course')
            ->where('course', '!=', '')
            ->distinct()
            ->orderBy('course')
            ->pluck('course')
            ->toArray();
        
        $allCourses = array_merge($allCourses, $dbCourses);
        $allCourses = array_unique($allCourses);
        sort($allCourses);

        // Get courses based on selected department
        if ($department && isset($departmentCourses[$department]) && !empty($departmentCourses[$department])) {
            $courses = collect($departmentCourses[$department]);
        } else {
            $courses = collect($allCourses);
        }

        $students = User::query()
            ->whereNotNull('student_id')
            ->when($searchId, function ($q) use ($searchId) {
                return $q->where('student_id', 'like', '%' . $searchId . '%')
                         ->orWhere('name', 'like', '%' . $searchId . '%');
            })
            ->when($department, function ($q) use ($department) {
                return $q->where('department', $department);
            })
            ->when($course && $course !== '' && $course !== null, function ($q) use ($course) {
                return $q->where('course', trim($course));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('students-status', compact('students', 'departments', 'department', 'courses', 'course', 'departmentCourses', 'allCourses', 'searchId'));
    }
}
