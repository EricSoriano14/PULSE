@extends('layouts.admin')

@section('title', 'Receive Report')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/receive-report.css') }}?v={{ @filemtime(public_path('css/receive-report.css')) }}">
@endsection

@section('content')
<div class="receive-report-page">
    <div class="page-header receive-report-header">
        <div>
            <h1 class="page-title">Receive Report</h1>
            <p class="subtext">Filter by department or course to view reports.</p>
        </div>

        <div class="records-toolbar">
            <form method="GET" action="{{ route('receive-report') }}" class="records-filter" id="filter-form">
                <label class="records-filter-label">
                    <span>Department</span>
                    <select name="department" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ (string)request('department') === (string)$dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="records-filter-label">
                    <span>Course</span>
                    <select name="course" onchange="this.form.submit()">
                        <option value="">All Courses</option>

                        @php
                            $dept = request('department');
                            $courseList = ($dept && isset($departmentCourses[$dept]))
                                ? $departmentCourses[$dept]
                                : [];
                        @endphp

                        @foreach($courseList as $c)
                            <option value="{{ $c }}" {{ (string)request('course') === (string)$c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="btn btn-primary">Apply</button>

                @if(request('department') || request('course'))
                    <a href="{{ route('receive-report') }}" class="btn btn-secondary btn-reset-filter">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="table-wrap">
            <table class="table receive-report-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Calamity</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="action-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        @php
                            $studentName =
                                $report->user?->full_name
                                ?? $report->user?->name
                                ?? ($report->student ? trim(($report->student->first_name ?? '').' '.($report->student->last_name ?? '')) : '')
                                ?? '';
                            $studentName = $studentName !== '' ? $studentName : '—';

                            $deptName =
                                $report->department
                                ?? $report->student?->department?->name
                                ?? $report->user?->department
                                ?? '—';

                            $courseName =
                                $report->student?->course?->name
                                ?? $report->user?->course
                                ?? '—';

                            $date = $report->submitted_at ?? $report->created_at;
                        @endphp

                        <tr>
                            <td>{{ $report->id }}</td>
                            <td>{{ $studentName }}</td>
                            <td>{{ $deptName }}</td>
                            <td>{{ $courseName }}</td>
                            <td>{{ $report->calamity }}</td>
                            <td title="{{ $report->description }}">
                                {{ \Illuminate\Support\Str::limit($report->description ?? '—', 60) }}
                            </td>
                            <td>{{ optional($date)->format('Y-m-d') }}</td>
                            <td>
                                <span class="status-badge status-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('receive-report.show', $report) }}" class="btn btn-secondary btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state-cell">
                                No reports found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            {{ $reports->links() }}
        </div>
    </div>
</div>
@endsection