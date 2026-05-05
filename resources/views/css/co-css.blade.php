@extends('layouts.admin')

@section('title', 'Manage Co-CSS')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/manage.css') }}?v={{ @filemtime(public_path('css/manage.css')) }}">
    <style>
        .flash-message {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 600;
        }

        .flash-success {
            background: #dcfce7;
            color: #166534;
        }

        .flash-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .flash-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .form-grid {
            display: grid;
            gap: 16px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-field span {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
        }

        .form-field input,
        .form-field select {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #fff;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: .2s ease;
        }

        .form-field input:focus,
        .form-field select:focus {
            border-color: #2f6f3a;
            box-shadow: 0 0 0 3px rgba(47, 111, 58, 0.12);
        }

        .table-wrap {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead th {
            text-align: left;
            font-size: 13px;
            font-weight: 800;
            color: #374151;
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #111827;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }

        .status-pill.active {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-empty {
            text-align: center;
            color: #6b7280;
            padding: 24px 12px !important;
        }

        .action-cell form {
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-soft-danger {
            background: #fff;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .btn-soft-danger:hover {
            background: #fef2f2;
        }

        .btn-soft-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #86efac;
        }

        .btn-soft-success:hover {
            background: #dcfce7;
        }

        .btn-soft-primary {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }

        .btn-soft-primary:hover {
            background: #dbeafe;
        }

        .panel-head p {
            margin: 6px 0 0;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal-card {
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            color: #111827;
        }

        .modal-close {
            border: none;
            background: #f3f4f6;
            color: #111827;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 8px;
            flex-wrap: wrap;
        }

        @media (max-width: 992px) {
            .panel-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1>Manage Co-CSS</h1>
            <p class="subtext">Create and manage Co-CSS accounts.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="flash-message flash-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flash-message flash-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="flash-message flash-warning">
            <strong>Please fix the following errors:</strong>
            <ul style="margin:8px 0 0 18px; padding-left: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel-grid" style="display:grid; grid-template-columns: minmax(320px, 430px) 1fr; gap:20px;">

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h3>Create Co-CSS</h3>
                    <p class="subtext">Add a new Co-CSS account for reassigned report review.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('css.cocss.store') }}" class="form-grid">
                @csrf

                <label class="form-field" for="name">
                    <span>Name</span>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
                </label>

                <label class="form-field" for="email">
                    <span>Email</span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
                </label>

                <label class="form-field" for="password">
                    <span>Password</span>
                    <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
                </label>

                <label class="form-field" for="department">
                    <span>Department</span>
                    <select id="department" name="department">
                        <option value="">Select Department</option>
                        <option value="ECOAST" {{ old('department') === 'ECOAST' ? 'selected' : '' }}>ECOAST</option>
                        <option value="PBS" {{ old('department') === 'PBS' ? 'selected' : '' }}>PBS</option>
                        <option value="PUMMA" {{ old('department') === 'PUMMA' ? 'selected' : '' }}>PUMMA</option>
                        <option value="RPSEA" {{ old('department') === 'RPSEA' ? 'selected' : '' }}>RPSEA</option>
                        <option value="CBHIS" {{ old('department') === 'CBHIS' ? 'selected' : '' }}>CBHIS</option>
                        <option value="SOC" {{ old('department') === 'SOC' ? 'selected' : '' }}>SOC</option>
                    </select>
                </label>

                <div style="padding-top: 4px;">
                    <button class="btn btn-primary" type="submit" style="width:100%; height:50px;">
                        Create Co-CSS
                    </button>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                <div>
                    <h3>Existing Co-CSS</h3>
                    <p class="subtext">View current Co-CSS accounts and manage their status.</p>
                </div>
                <div style="padding:8px 12px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:13px; font-weight:700;">
                    {{ $coCssUsers->count() }} account(s)
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>DEPARTMENT</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($coCssUsers as $user)
                            @php
                                $isActive = strtolower((string) ($user->status ?? 'active')) === 'active';
                            @endphp

                            <tr>
                                <td style="font-weight:700;">{{ $user->full_name ?: $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->department ?: '-' }}</td>
                                <td>
                                    <span class="status-pill {{ $isActive ? 'active' : 'inactive' }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <button
                                            type="button"
                                            class="btn btn-soft-primary edit-cocss-btn"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->full_name ?: $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-department="{{ $user->department ?? '' }}"
                                        >
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('css.cocss.toggle', $user) }}">
                                            @csrf
                                            <button
                                                class="btn {{ $isActive ? 'btn-soft-danger' : 'btn-soft-success' }}"
                                                type="submit">
                                                {{ $isActive ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="table-empty">No Co-CSS users yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <div class="modal-backdrop" id="editModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Edit Co-CSS</h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">×</button>
            </div>

            <div class="modal-body">
                <form method="POST" id="editCoCssForm" class="form-grid">
                    @csrf
                    @method('PUT')

                    <label class="form-field" for="edit_name">
                        <span>Name</span>
                        <input type="text" id="edit_name" name="name" placeholder="Enter full name" required>
                    </label>

                    <label class="form-field" for="edit_email">
                        <span>Email</span>
                        <input type="email" id="edit_email" name="email" placeholder="Enter email address" required>
                    </label>

                    <label class="form-field" for="edit_department">
                        <span>Department</span>
                        <select id="edit_department" name="department">
                            <option value="">Select Department</option>
                            <option value="ECOAST">ECOAST</option>
                            <option value="PBS">PBS</option>
                            <option value="PUMMA">PUMMA</option>
                            <option value="RPSEA">RPSEA</option>
                            <option value="CBHIS">CBHIS</option>
                            <option value="SOC">SOC</option>
                        </select>
                    </label>

                    <label class="form-field" for="edit_password">
                        <span>New Password <small style="font-weight:500; color:#6b7280;">(optional)</small></span>
                        <input type="password" id="edit_password" name="password" placeholder="Leave blank to keep current password">
                    </label>

                    <div class="modal-actions">
                        <button type="button" class="btn" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Co-CSS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, name, email, department) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editCoCssForm');

            form.action = `/css/co-css/${id}`;
            document.getElementById('edit_name').value = name || '';
            document.getElementById('edit_email').value = email || '';
            document.getElementById('edit_department').value = department || '';
            document.getElementById('edit_password').value = '';

            modal.classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.edit-cocss-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    openEditModal(
                        this.dataset.id,
                        this.dataset.name,
                        this.dataset.email,
                        this.dataset.department
                    );
                });
            });

            window.addEventListener('click', function (e) {
                const modal = document.getElementById('editModal');
                if (e.target === modal) {
                    closeEditModal();
                }
            });
        });
    </script>
@endsection