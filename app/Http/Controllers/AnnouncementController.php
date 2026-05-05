<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $announcements = Announcement::query()
            ->latest('id')
            ->paginate(10);

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
            'SOC' => [
                'Bachelor of Science in Criminology',
            ],
            'CBIHS' => [
                'Bachelor of Science in Nursing',
                'Bachelor of Science in Pharmacy',
            ],
        ];

        return view('announcements', compact('announcements', 'departmentCourses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
            'department'  => ['required', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'max:5120'],
        ]);

        $department = $this->normalizeDepartment($data['department']);

        $imagePath = null;
        $imageUrl  = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
            $imageUrl = Storage::disk('public')->url($imagePath);
        }

        $announcement = Announcement::create([
            'user_id' => Auth::id(),
            'posted_by_user_id' => Auth::id(),
            'department' => $department,
            'description' => $data['description'],
            'image_path' => $imagePath,
            'image_url'  => $imageUrl,
            'status' => 'active',
            'canceled_at' => null,
        ]);

        $this->notifyStudentsAboutAnnouncement($announcement);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement posted successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->status === 'canceled') {
            return back()->with('success', 'Announcement already canceled.');
        }

        $announcement->status = 'canceled';
        $announcement->canceled_at = now();
        $announcement->save();

        return back()->with('success', 'Announcement canceled.');
    }

    private function notifyStudentsAboutAnnouncement(Announcement $announcement): void
    {
        $students = User::query()
            ->where('role', 'student')
            ->where('status', 'active')
            ->get();

        if ($students->isEmpty()) {
            return;
        }

        $postedBy = Auth::user();
        $message = $this->buildAnnouncementMessage($announcement);

        $this->notificationService->createForUsers(
            $students,
            Notification::TYPE_STUDENT_ANNOUNCEMENT,
            'New Announcement',
            $message,
            null,
            $announcement,
            $postedBy,
            null
        );
    }

    private function buildAnnouncementMessage(Announcement $announcement): string
    {
        $department = trim((string) $announcement->department);
        $description = trim(strip_tags((string) $announcement->description));

        $base = $description !== ''
            ? Str::limit($description, 120)
            : 'Admin posted a new announcement.';

        if ($department !== '') {
            return "[{$department}] {$base}";
        }

        return $base;
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