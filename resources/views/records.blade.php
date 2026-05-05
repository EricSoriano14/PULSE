@extends('layouts.admin')

@section('title', 'Records')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/records.css') }}?v={{ @filemtime(public_path('css/records.css')) }}">
@endsection

@section('content')
    <div class="records-page">
        <div class="page-header records-header">
            <div>
                <h1 class="page-title">Records</h1>
                <p class="subtext">Filter by department or course to view student records.</p>
            </div>

            <div class="records-toolbar">
                <form method="GET" action="{{ route('records') }}" class="records-filter" id="filter-form">
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
                            @foreach($courses as $c)
                                <option value="{{ $c }}" {{ (string)request('course') === (string)$c ? 'selected' : '' }}>
                                    {{ $c }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="records-filter-label">
                        <span>Search by Student ID</span>
                        <input
                            type="text"
                            name="search_id"
                            placeholder="Enter student ID"
                            value="{{ request('search_id') }}"
                            onkeydown="if(event.key==='Enter'){this.form.submit();}"
                        >
                    </label>

                    <button type="submit" class="btn btn-primary">Apply</button>

                    @if(request('department') || request('course') || request('search_id'))
                        <a href="{{ route('records') }}" class="btn btn-secondary btn-reset-filter">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="records-grid">
            @forelse($records as $record)
                <a class="record-card" href="{{ route('records.show', $record) }}">
                    <div class="record-title">{{ $record->user?->name ?? '—' }}</div>

                    <div class="record-meta">
                        <div class="record-row">
                            <span class="record-label">ID</span>
                            <span class="record-value">{{ $record->user?->student_id ?? $record->id }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Course</span>
                            <span class="record-value">{{ $record->user?->course ?? '—' }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Department</span>
                            <span class="record-value">{{ $record->department ?? ($record->user?->department ?? '—') }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Status</span>
                            <span class="record-value">{{ ucfirst($record->status) }}</span>
                        </div>

                        <div class="record-row">
                            <span class="record-label">Description</span>
                            <span class="record-value" title="{{ $record->description }}">
                                {{ \Illuminate\Support\Str::limit($record->description ?? '—', 60) }}
                            </span>
                        </div>
                    </div>

                    <div class="record-actions">
                        <span class="btn btn-secondary btn-sm">View</span>
                    </div>
                </a>
            @empty
                <div class="panel">
                    <p class="subtext empty-records-text">No records found.</p>
                </div>
            @endforelse
        </div>

        <div class="table-pagination">
            {{ $records->links() }}
        </div>
    </div>
@endsection