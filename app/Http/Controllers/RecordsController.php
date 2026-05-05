<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecordsController extends Controller
{
    public function index(Request $request)
    {
        $department = $this->normalizeDepartment($request->query('department'));
        $courseParam = trim((string) $request->query('course', ''));
        $searchId = trim((string) $request->query('search_id', ''));

        $course = ($courseParam === '') ? null : $courseParam;

        $departments = collect(['ECOAST', 'PBS', 'PUMMA', 'RPSEA', 'CBHIS', 'SOC']);

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

        $allCourses = [];
        foreach ($departmentCourses as $dept => $deptCourses) {
            foreach ($deptCourses as $courseName) {
                if (!in_array($courseName, $allCourses, true)) {
                    $allCourses[] = $courseName;
                }
            }
        }

        $dbCourses = \App\Models\User::query()
            ->whereNotNull('course')
            ->where('course', '!=', '')
            ->distinct()
            ->orderBy('course')
            ->pluck('course')
            ->toArray();

        $allCourses = array_merge($allCourses, $dbCourses);
        $allCourses = array_unique($allCourses);
        sort($allCourses);

        if ($department && isset($departmentCourses[$department]) && !empty($departmentCourses[$department])) {
            $courses = collect($departmentCourses[$department]);
        } else {
            $courses = collect($allCourses);
        }

        $user = Auth::user();
        $role = strtolower(trim((string) ($user?->role ?? '')));

        $records = Report::query()
            ->with(['user', 'assignedFaculty', 'assignedCoCss'])
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
            ->when($searchId, function ($q) use ($searchId) {
                return $q->where(function ($query) use ($searchId) {
                    $query->where('id', 'like', '%' . $searchId . '%')
                        ->orWhereHas('user', function ($userQuery) use ($searchId) {
                            $userQuery->where('student_id', 'like', '%' . $searchId . '%');
                        });
                });
            })
            ->when($department !== '', function ($q) use ($department) {
                $aliases = $this->departmentAliases($department);

                return $q->where(function ($query) use ($aliases) {
                    $query->whereIn('department', $aliases)
                        ->orWhereHas('user', function ($userQuery) use ($aliases) {
                            $userQuery->whereIn('department', $aliases);
                        });
                });
            })
            ->when($course && $course !== '' && $course !== null, function ($q) use ($course) {
                return $q->whereHas('user', function ($userQuery) use ($course) {
                    $userQuery->where('course', trim($course));
                });
            })
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('records', compact('records', 'departments', 'department', 'courses', 'course', 'departmentCourses', 'allCourses', 'searchId'));
    }

    public function show(Report $report)
    {
        $report->load(['user', 'action', 'assignedFaculty', 'assignedCoCss']);

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

        return view('records-show', compact('report'));
    }

    public function destroy(Report $report)
    {
        abort(403);
    }

    public function export(Request $request)
    {
        $format = strtolower($request->query('format', 'csv'));
        $department = $this->normalizeDepartment($request->query('department'));
        $course = $request->query('course');
        $searchId = trim((string) $request->query('search_id', ''));

        $user = Auth::user();
        $role = strtolower(trim((string) ($user?->role ?? '')));

        $query = Report::query()
            ->with(['user', 'assignedFaculty', 'assignedCoCss'])
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
            ->when($searchId, function ($q) use ($searchId) {
                return $q->where(function ($query) use ($searchId) {
                    $query->where('id', 'like', '%' . $searchId . '%')
                        ->orWhereHas('user', function ($userQuery) use ($searchId) {
                            $userQuery->where('student_id', 'like', '%' . $searchId . '%');
                        });
                });
            })
            ->when($department !== '', function ($q) use ($department) {
                $aliases = $this->departmentAliases($department);

                return $q->where(function ($query) use ($aliases) {
                    $query->whereIn('department', $aliases)
                        ->orWhereHas('user', function ($userQuery) use ($aliases) {
                            $userQuery->whereIn('department', $aliases);
                        });
                });
            })
            ->when($course, function ($q) use ($course) {
                return $q->whereHas('user', function ($userQuery) use ($course) {
                    $userQuery->where('course', $course);
                });
            })
            ->latest('submitted_at')
            ->latest('id');

        $reports = $query->get();

        $rows = [];
        $rows[] = ['ID', 'Student', 'Student ID', 'Email', 'Course', 'Calamity', 'Department', 'Assigned Faculty', 'Assigned Co-CSS', 'Status', 'Submitted At'];

        foreach ($reports as $report) {
            $rows[] = [
                $report->id,
                $report->user?->name ?? '',
                $report->user?->student_id ?? '',
                $report->user?->email ?? '',
                $report->user?->course ?? '',
                $report->calamity,
                $this->normalizeDepartment($report->department ?? ''),
                $report->assignedFaculty?->name ?? '',
                $report->assignedCoCss?->name ?? '',
                $report->status,
                optional($report->submitted_at)->toDateTimeString(),
            ];
        }

        switch ($format) {
            case 'pdf':
                return $this->exportPdf($reports, $rows);
            case 'docx':
                return $this->exportDocx($reports, $rows);
            case 'xlsx':
                return $this->exportXlsx($reports, $rows);
            case 'csv':
            default:
                return $this->exportCsv($rows);
        }
    }

    private function exportCsv(array $rows): StreamedResponse
    {
        $filename = 'records.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportPdf($reports, array $rows)
    {
        $filename = 'records.pdf';

        $html = '<html><head><meta charset="UTF-8"><title>Records Export</title></head><body>';
        $html .= '<h1>Records Export</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';

        foreach ($rows as $rowIndex => $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $tag = $rowIndex === 0 ? 'th' : 'td';
                $html .= '<' . $tag . '>' . e((string) $cell) . '</' . $tag . '>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportDocx($reports, array $rows)
    {
        $content = '';
        foreach ($rows as $row) {
            $content .= implode("\t", array_map(fn ($v) => (string) $v, $row)) . PHP_EOL;
        }

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="records.docx"',
        ]);
    }

    private function exportXlsx($reports, array $rows)
    {
        $content = '';
        foreach ($rows as $row) {
            $content .= implode(",", array_map(function ($value) {
                $value = str_replace('"', '""', (string) $value);
                return "\"{$value}\"";
            }, $row)) . PHP_EOL;
        }

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="records.xlsx"',
        ]);
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