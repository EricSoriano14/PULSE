@extends('layouts.admin')

@section('title', 'Students Status')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/students-status.css') }}?v={{ @filemtime(public_path('css/students-status.css')) }}">
@endsection

@section('content')
    <div class="students-status-page">
        <div class="page-header" style="margin-bottom: 12px;">
            <div>
                <h1 style="margin: 0;">Students Status</h1>
                <p class="subtext">Filter by department or course to view student status.</p>
            </div>
            <div class="students-toolbar">
                <form method="GET" action="{{ route('students-status') }}" class="students-filter" id="filter-form">
                    <label class="students-filter-label">
                        <span>Department & Course</span>
                        <select name="filter" id="department-course-select" onchange="handleFilterChange()">
                            <option value="">All Departments & Courses</option>
                            @foreach($departments as $dept)
                                @if(isset($departmentCourses[$dept]) && !empty($departmentCourses[$dept]))
                                    <optgroup label="{{ $dept }}">
                                        <option value="dept:{{ $dept }}" @selected($department === $dept && !$course)>
                                            {{ $dept }} - All Courses
                                        </option>
                                        @foreach($departmentCourses[$dept] as $crs)
                                            <option value="dept:{{ $dept }}|course:{{ htmlspecialchars($crs, ENT_QUOTES, 'UTF-8') }}" @selected($department === $dept && trim($course ?? '') === trim($crs))>
                                                {{ $crs }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <optgroup label="{{ $dept }}">
                                        <option value="dept:{{ $dept }}" @selected($department === $dept && !$course)>
                                            {{ $dept }} - All Courses
                                        </option>
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" name="department" id="department-input" value="{{ isset($department) && $department ? $department : '' }}">
                        <input type="hidden" id="course-input" value="">
                    </label>
                    <label class="students-filter-label">
                        <span>Search ID</span>
                        <input type="text" name="search_id" id="search-id-input" placeholder="Enter student ID or name" value="{{ request('search_id') }}" class="search-input">
                    </label>
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="panel" style="margin-bottom: 12px; color: #166534; background: #ecfdf3;">
                {{ session('success') }}
            </div>
        @endif

        @if(!$department && !$course && !request('search_id'))
            <div class="panel">
                <p class="subtext" style="margin:0;">Select a department or course, or search by ID to view students.</p>
            </div>
        @else
            <div class="students-grid">
                @forelse($students as $student)
                    <div class="student-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div class="student-title">{{ $student->name ?? '—' }}</div>
                            <span class="safety-badge safety-{{ $student->safety_status ?? 'safe' }}">
                                {{ ($student->safety_status ?? 'safe') === 'safe' ? 'Safe' : 'At Risk' }}
                            </span>
                        </div>
                        <div class="student-meta">
                            <div class="student-row">
                                <span class="student-label">Student ID</span>
                                <span class="student-value">{{ $student->student_id ?? '—' }}</span>
                            </div>
                            <div class="student-row">
                                <span class="student-label">Email</span>
                                <span class="student-value">{{ $student->email ?? '—' }}</span>
                            </div>
                            <div class="student-row">
                                <span class="student-label">Course</span>
                                <span class="student-value">{{ $student->course ?? '—' }}</span>
                            </div>
                            <div class="student-row">
                                <span class="student-label">Department</span>
                                <span class="student-value">{{ $student->department ?? '—' }}</span>
                            </div>
                            @if($student->year)
                                <div class="student-row">
                                    <span class="student-label">Year</span>
                                    <span class="student-value">{{ $student->year ?? '—' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="panel">
                        <p class="subtext" style="margin:0;">
                            No students found
                            @if(request('search_id'))
                                for ID <strong>{{ request('search_id') }}</strong>
                            @endif
                            @if($department)
                                {{ request('search_id') ? 'and' : 'for' }} department <strong>{{ $department }}</strong>
                            @endif
                            @if(isset($course) && $course && $course !== '')
                                {{ ($department || request('search_id')) ? 'and' : 'for' }} course <strong>{{ $course }}</strong>
                            @endif
                            .
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- ✅ Pagination (fixed to match modern layout like other pages) --}}
            @if($students->hasPages())
                <div class="pagination-wrap">
                    {{ $students->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>

    <script>
        function handleFilterChange() {
            const select = document.getElementById('department-course-select');
            const departmentInput = document.getElementById('department-input');
            const courseInput = document.getElementById('course-input');
            const selectedValue = select.value;

            // Reset both inputs
            departmentInput.value = '';
            courseInput.value = '';

            if (selectedValue && selectedValue !== '') {
                // Parse the value (format: "dept:ECOAST" or "dept:ECOAST|course:Course Name")
                if (selectedValue.includes('|')) {
                    // Both department and course selected
                    const parts = selectedValue.split('|');
                    const deptPart = parts[0]; // First part is always department
                    const coursePart = parts[1]; // Second part is always course

                    if (deptPart && deptPart.startsWith('dept:')) {
                        departmentInput.value = deptPart.replace('dept:', '');
                    }
                    if (coursePart && coursePart.startsWith('course:')) {
                        // Extract course value (browser will handle URL encoding on submit)
                        const courseValue = coursePart.replace('course:', '');
                        courseInput.value = courseValue;
                        courseInput.setAttribute('name', 'course'); // Add name attribute so it gets submitted
                    }
                } else if (selectedValue.startsWith('dept:')) {
                    // Only department selected - explicitly clear course
                    departmentInput.value = selectedValue.replace('dept:', '');
                    courseInput.value = ''; // Make sure course is cleared
                } else if (selectedValue === '') {
                    // All selected - clear both
                    departmentInput.value = '';
                    courseInput.value = '';
                }
            } else {
                // No selection - clear both
                departmentInput.value = '';
                courseInput.value = '';
            }

            // Remove course input name attribute if it's empty to prevent sending empty parameter
            if (courseInput.value === '' || courseInput.value === null || !courseInput.value.trim()) {
                courseInput.removeAttribute('name');
            } else {
                courseInput.setAttribute('name', 'course');
            }

            // Submit the form
            document.getElementById('filter-form').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Set initial values for hidden inputs and dropdown
            const departmentInput = document.getElementById('department-input');
            const courseInput = document.getElementById('course-input');
            const select = document.getElementById('department-course-select');

            @if(isset($department) && $department)
                departmentInput.value = '{{ $department }}';
            @endif

            @if(isset($course) && $course && $course !== '')
                courseInput.value = '{{ addslashes($course) }}';
                courseInput.setAttribute('name', 'course');
            @endif

            // Set initial selected option
            @if(isset($department) && $department && isset($course) && $course && $course !== '')
                // Build the target value to match
                const targetDept = '{{ $department }}';
                const targetCourse = '{{ addslashes($course) }}';
                const targetValue = 'dept:' + targetDept + '|course:' + targetCourse;

                // Try to find matching option
                for (let i = 0; i < select.options.length; i++) {
                    const opt = select.options[i];
                    if (opt.value === targetValue) {
                        select.selectedIndex = i;
                        break;
                    }
                }
            @elseif(isset($department) && $department)
                const targetDept = '{{ $department }}';
                const targetValue = 'dept:' + targetDept;
                for (let i = 0; i < select.options.length; i++) {
                    const opt = select.options[i];
                    if (opt.value === targetValue) {
                        select.selectedIndex = i;
                        break;
                    }
                }
            @else
                select.value = '';
            @endif
        });
    </script>
@endsection
