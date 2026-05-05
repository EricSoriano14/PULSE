@extends('layouts.admin')

@section('title', 'Manage Students')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/manage.css') }}?v={{ @filemtime(public_path('css/manage.css')) }}">
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1>Manage Students</h1>
            <p class="subtext">Add, remove, and filter students by department.</p>
        </div>
    </div>

    <div class="panel-grid">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h3>Add student</h3>
                    <p class="subtext">Include student name, ID#, and department.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('manage.store') }}" id="student-form" class="form-grid">
                @csrf

                <label class="form-field">
                    <span>First Name</span>
                    <input id="first_name" name="first_name" type="text" placeholder="e.g., Alex" required>
                </label>

                <label class="form-field">
                    <span>Last Name</span>
                    <input id="last_name" name="last_name" type="text" placeholder="e.g., Rivera" required>
                </label>

                <label class="form-field">
                    <span>ID #</span>
                    <input id="student-id" name="student_id" type="text" placeholder="e.g., 2024-1034" required>
                </label>

                <label class="form-field">
                    <span>Email</span>
                    <input id="email" name="email" type="email" placeholder="e.g., alex@example.com" required>
                </label>

                <label class="form-field">
                    <span>Department & Course</span>
                    <select id="student-dept-course" name="filter" required onchange="handleAddStudentFilterChange()">
                        <option value="">Select Department & Course</option>
                        @foreach($allDepartments as $dept)
                            @if(isset($departmentCourses[$dept]) && !empty($departmentCourses[$dept]))
                                <optgroup label="{{ $dept }}">
                                    <option value="dept:{{ $dept }}">
                                        {{ $dept }} - All Courses
                                    </option>
                                    @foreach($departmentCourses[$dept] as $crs)
                                        <option value="dept:{{ $dept }}|course:{{ $crs }}">
                                            {{ $crs }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <optgroup label="{{ $dept }}">
                                    <option value="dept:{{ $dept }}">
                                        {{ $dept }} - All Courses
                                    </option>
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <input type="hidden" name="department" id="student-dept-input" value="">
                    <input type="hidden" name="course" id="student-course-input" value="">
                </label>

                <label class="form-field">
                    <span>Year</span>
                    <select id="year" name="year">
                        <option value="">Select year</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </label>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add student</button>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h3>Filter & Search</h3>
                    <p class="subtext">Filter students by ID, department, course, or year.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('manage') }}" class="filter-grid" id="filter-form">
                <label class="form-field">
                    <span>Student ID</span>
                    <input name="q" id="filter-search" type="text" placeholder="Search by student ID" value="{{ request('q') }}">
                </label>

                <label class="form-field">
                    <span>Department & Course</span>
                    <select name="filter" id="department-course-select" onchange="handleFilterChange()">
                        <option value="">All Departments & Courses</option>
                        @foreach($allDepartments as $dept)
                            @if(isset($departmentCourses[$dept]) && !empty($departmentCourses[$dept]))
                                <optgroup label="{{ $dept }}">
                                    <option value="dept:{{ $dept }}" @selected(request('department') === $dept && !request('course'))>
                                        {{ $dept }} - All Courses
                                    </option>
                                    @foreach($departmentCourses[$dept] as $crs)
                                        <option value="dept:{{ $dept }}|course:{{ $crs }}" @selected(request('department') === $dept && request('course') === $crs)>
                                            {{ $crs }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <optgroup label="{{ $dept }}">
                                    <option value="dept:{{ $dept }}" @selected(request('department') === $dept && !request('course'))>
                                        {{ $dept }} - All Courses
                                    </option>
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <input type="hidden" name="department" id="department-input" value="{{ request('department') ?? '' }}">
                    <input type="hidden" name="course" id="course-input" value="{{ request('course') ?? '' }}">
                </label>

                <label class="form-field">
                    <span>Year</span>
                    <select name="year" id="filter-year" onchange="document.getElementById('filter-form').submit();">
                        <option value="">Select Year</option>
                        <option value="1" @selected(request('year') == '1')>1st Year</option>
                        <option value="2" @selected(request('year') == '2')>2nd Year</option>
                        <option value="3" @selected(request('year') == '3')>3rd Year</option>
                        <option value="4" @selected(request('year') == '4')>4th Year</option>
                    </select>
                </label>

                <div class="form-actions">
                    <a href="{{ route('manage') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </section>
    </div>

    <section class="panel">
        <div class="panel-head">
            <h3>Students</h3>
            <span class="pill">
                Showing {{ $users->total() }} student(s)
            </span>
        </div>

        <div class="table-wrap">
            <table class="data-table" id="students-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>ID #</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->student_id }}</td>
                            <td>{{ $user->department ?? '—' }}</td>
                            <td>{{ $user->course ?? '—' }}</td>
                            <td>
                                <form method="POST"
                                      action="{{ route('manage.destroy', $user) }}"
                                      class="inline-action-form"
                                      onsubmit="return confirm('Are you sure you want to remove this student?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            {{ $users->links('vendor.pagination.default') }}
        </div>
    </section>

    <script>
        function handleFilterChange() {
            const select = document.getElementById('department-course-select');
            const departmentInput = document.getElementById('department-input');
            const courseInput = document.getElementById('course-input');
            const selectedValue = select.value;

            departmentInput.value = '';
            courseInput.value = '';

            if (selectedValue && selectedValue !== '') {
                if (selectedValue.includes('|')) {
                    const parts = selectedValue.split('|');
                    const deptPart = parts.find(p => p.startsWith('dept:'));
                    const coursePart = parts.find(p => p.startsWith('course:'));

                    if (deptPart) {
                        departmentInput.value = deptPart.replace('dept:', '');
                    }
                    if (coursePart) {
                        courseInput.value = coursePart.replace('course:', '');
                    }
                } else if (selectedValue.startsWith('dept:')) {
                    departmentInput.value = selectedValue.replace('dept:', '');
                }
            }

            document.getElementById('filter-form').submit();
        }

        function handleAddStudentFilterChange() {
            const select = document.getElementById('student-dept-course');
            const departmentInput = document.getElementById('student-dept-input');
            const courseInput = document.getElementById('student-course-input');
            const selectedValue = select.value;

            departmentInput.value = '';
            courseInput.value = '';

            if (selectedValue && selectedValue !== '') {
                if (selectedValue.includes('|')) {
                    const parts = selectedValue.split('|');
                    const deptPart = parts.find(p => p.startsWith('dept:'));
                    const coursePart = parts.find(p => p.startsWith('course:'));

                    if (deptPart) {
                        departmentInput.value = deptPart.replace('dept:', '');
                    }
                    if (coursePart) {
                        courseInput.value = coursePart.replace('course:', '');
                    }
                } else if (selectedValue.startsWith('dept:')) {
                    departmentInput.value = selectedValue.replace('dept:', '');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('department-course-select');

            @if(request('department') && request('course'))
                select.value = 'dept:{{ request('department') }}|course:{{ request('course') }}';
            @elseif(request('department'))
                select.value = 'dept:{{ request('department') }}';
            @endif

            const searchInput = document.getElementById('filter-search');
            let searchTimeout;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);

                    searchTimeout = setTimeout(function() {
                        document.getElementById('filter-form').submit();
                    }, 500);
                });
            }
        });
    </script>
@endsection