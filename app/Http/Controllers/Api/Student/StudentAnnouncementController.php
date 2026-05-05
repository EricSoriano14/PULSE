<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Student\StudentAnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;

class StudentAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // IMPORTANT: keep it safe + student scoped, but don't break if student profile missing
        $student = $user?->student;

        $query = Announcement::query()
            // ✅ Exclude canceled announcements
            ->where('status', 'active')
            ->latest('id');

        // ✅ Apply targeting only if student exists
        if ($student) {
            $deptId = $student->department_id;
            $courseId = $student->course_id;

            // Department targeting: null = visible to all
            $query->where(function ($q) use ($deptId) {
                $q->whereNull('department_target_id')
                  ->orWhere('department_target_id', $deptId);
            });

            // Course targeting: null = visible to all
            $query->where(function ($q) use ($courseId) {
                $q->whereNull('course_target_id')
                  ->orWhere('course_target_id', $courseId);
            });
        }

        // Keep predictable output + pagination (mobile-friendly)
        $announcements = $query->paginate(10);

        return StudentAnnouncementResource::collection($announcements);
    }
}
