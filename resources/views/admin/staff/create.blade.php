@extends('layouts.admin')

@section('title', 'Add Staff')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/manage.css') }}?v={{ @filemtime(public_path('css/manage.css')) }}">
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1>Add Staff</h1>
            <p class="subtext">Create a new Faculty, CSS, or Co-CSS account.</p>
        </div>
    </div>

    @if($errors->any())
        <div style="margin-bottom:16px; padding:14px 16px; border-radius:12px; background:#fee2e2; color:#991b1b;">
            <strong>Please fix the following errors:</strong>
            <ul style="margin:8px 0 0 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel-grid">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h3>New staff account</h3>
                    <p class="subtext">Faculty accounts need a department so students can select them in the Flutter report page.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.staff.store') }}" class="form-grid">
                @csrf

                <label class="form-field">
                    <span>Full Name</span>
                    <input
                        name="full_name"
                        type="text"
                        value="{{ old('full_name') }}"
                        placeholder="e.g., Juan Dela Cruz"
                        required>
                </label>

                <label class="form-field">
                    <span>Email</span>
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="e.g., faculty@school.edu"
                        required>
                </label>

                <label class="form-field">
                    <span>Role</span>
                    <select name="role" id="role" required>
                        <option value="">Select Role</option>
                        <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="css" {{ old('role') === 'css' ? 'selected' : '' }}>CSS</option>
                        <option value="co_css" {{ old('role') === 'co_css' ? 'selected' : '' }}>Co-CSS</option>
                    </select>
                </label>

                <label class="form-field" id="department-field">
                    <span>Department</span>
                    <select name="department">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field">
                    <span>Password</span>
                    <input
                        name="password"
                        type="password"
                        placeholder="Minimum 8 characters"
                        required>
                </label>

                <label class="form-field">
                    <span>Confirm Password</span>
                    <input
                        name="password_confirmation"
                        type="password"
                        placeholder="Re-enter password"
                        required>
                </label>

                <div class="form-field" style="justify-content:end;">
                    <span>&nbsp;</span>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Create Staff Account</button>
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <script>
        const roleSelect = document.getElementById('role');
        const departmentField = document.getElementById('department-field');

        function toggleDepartmentField() {
            const role = roleSelect.value;
            departmentField.style.display = (role === 'faculty') ? 'flex' : 'none';
        }

        roleSelect.addEventListener('change', toggleDepartmentField);
        toggleDepartmentField();
    </script>
@endsection