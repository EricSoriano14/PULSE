@extends('layouts.admin')

@section('title', 'Manage Staff')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/manage.css') }}?v={{ @filemtime(public_path('css/manage.css')) }}">
    <style>
        .status-pill,
        .role-pill,
        .count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .count-pill {
            background: #eef2ff;
            color: #4338ca;
        }

        .role-pill {
            background: #e0f2fe;
            color: #075985;
            text-transform: uppercase;
        }

        .status-pill.active {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        .action-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-inline {
            border: none;
            background: transparent;
            padding: 0;
        }

        .btn-enable,
        .btn-disable {
            border-radius: 14px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: .2s ease;
        }

        .btn-enable {
            background: #2f7d3b;
            color: #fff;
            border: 1px solid #2f7d3b;
        }

        .btn-enable:hover {
            background: #276732;
        }

        .btn-disable {
            background: #fff;
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .btn-disable:hover {
            background: #fff5f5;
        }

        .empty-row {
            text-align: center;
            padding: 24px;
            color: #6b7280;
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h1>Manage Staff</h1>
        <p class="subtext">Add, enable, disable, and filter Faculty, CSS, and Co-CSS accounts.</p>
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px; padding:14px 16px; border-radius:16px; background:#dcfce7; color:#166534; font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:16px; padding:14px 16px; border-radius:16px; background:#fee2e2; color:#991b1b; font-weight:700;">
            Please check the form fields and try again.
        </div>
    @endif

    <div class="panel-grid">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h3>Add staff</h3>
                    <p class="subtext">Create a Faculty, CSS, or Co-CSS account.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.staff.store') }}" class="form-grid">
                @csrf

                <label class="form-field">
                    <span>Full Name</span>
                    <input
                        type="text"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        placeholder="e.g., Juan Dela Cruz"
                        required>
                </label>

                <label class="form-field">
                    <span>Email</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="e.g., juan@school.edu"
                        required>
                </label>

                <label class="form-field">
                    <span>Password</span>
                    <input
                        type="password"
                        name="password"
                        placeholder="Minimum 8 characters"
                        required>
                </label>

                <label class="form-field">
                    <span>Confirm Password</span>
                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Re-enter password"
                        required>
                </label>

                <label class="form-field">
                    <span>Role</span>
                    <select name="role" id="role" required>
                        <option value="">Select role</option>
                        <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="css" {{ old('role') === 'css' ? 'selected' : '' }}>CSS</option>
                        <option value="co_css" {{ old('role') === 'co_css' ? 'selected' : '' }}>Co-CSS</option>
                    </select>
                </label>

                <label class="form-field" id="department-field">
                    <span>Department</span>
                    <select name="department">
                        <option value="">Select Department</option>
                        <option value="ECOAST" {{ old('department') === 'ECOAST' ? 'selected' : '' }}>ECOAST</option>
                        <option value="PBS" {{ old('department') === 'PBS' ? 'selected' : '' }}>PBS</option>
                        <option value="PUMMA" {{ old('department') === 'PUMMA' ? 'selected' : '' }}>PUMMA</option>
                        <option value="RPSEA" {{ old('department') === 'RPSEA' ? 'selected' : '' }}>RPSEA</option>
                        <option value="CBHIS" {{ old('department') === 'CBHIS' ? 'selected' : '' }}>CBHIS</option>
                        <option value="SOC" {{ old('department') === 'SOC' ? 'selected' : '' }}>SOC</option>
                    </select>
                </label>

                <div class="form-field" style="justify-content:end;">
                    <span>&nbsp;</span>
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 54px;">
                        Add staff
                    </button>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h3>Filter & Search</h3>
                <p class="subtext">Filter staff by name, email, role, or department.</p>
            </div>

            <form method="GET" action="{{ route('admin.staff.index') }}" class="form-grid">
                <label class="form-field">
                    <span>Search</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search by name, email, or department">
                </label>

                <label class="form-field">
                    <span>Role</span>
                    <select name="role">
                        <option value="">All Staff Roles</option>
                        <option value="faculty" {{ request('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="css" {{ request('role') === 'css' ? 'selected' : '' }}>CSS</option>
                        <option value="co_css" {{ request('role') === 'co_css' ? 'selected' : '' }}>Co-CSS</option>
                    </select>
                </label>

                <div class="form-field" style="justify-content:end;">
                    <span>&nbsp;</span>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <section class="panel" style="margin-top:20px;">
        <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <h3>Staff</h3>
            </div>
            <div class="count-pill">
                Showing {{ $staffUsers->total() }} staff account(s)
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>NAME</th>
                        <th>EMAIL</th>
                        <th>ROLE</th>
                        <th>DEPARTMENT</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffUsers as $staff)
                        <tr>
                            <td>{{ $staff->full_name ?: $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td><span class="role-pill">{{ $staff->role }}</span></td>
                            <td>{{ $staff->department ?: '-' }}</td>
                            <td>
                                <span class="status-pill {{ $staff->status }}">
                                    {{ ucfirst($staff->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <form method="POST" action="{{ route('admin.staff.toggle', $staff) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn-inline {{ $staff->status === 'active' ? 'btn-disable' : 'btn-enable' }}">
                                            {{ $staff->status === 'active' ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">No staff accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">
            {{ $staffUsers->links() }}
        </div>
    </section>

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