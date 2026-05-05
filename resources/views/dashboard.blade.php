@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));
        $roleLabel = $role === 'admin'
            ? 'Administrator'
            : (in_array($role, ['css', 'faculty', 'co_css'], true) ? 'Staff' : 'User');

        $totalReports = (int) ($counts['reports'] ?? 0);
        $pending = (int) ($counts['pending'] ?? 0);
        $accepted = (int) ($counts['accepted'] ?? 0);
        $declined = (int) ($counts['declined'] ?? 0);
    @endphp

    <div class="dashboard-page">
        <div class="page-header">
            <div>
                <h1>Dashboard</h1>
                <p class="page-subtitle">System overview for the current term</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="search">
                <input
                    type="text"
                    name="q"
                    placeholder="Search reports, students, cases..."
                    value="{{ request('q') }}"
                >
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="dashboard-header-card">
            <div class="dashboard-header-main">
                <div class="badge-line">
                    <span class="badge-label">Term / School Year</span>
                    <span class="badge-pill badge-pill-success">Active</span>
                </div>

                <div class="header-grid">
                    <div>
                        <div class="header-label">System</div>
                        <div class="header-value">Incident Reporting &amp; Safety</div>
                    </div>
                    <div>
                        <div class="header-label">Role</div>
                        <div class="header-value">{{ $roleLabel }}</div>
                    </div>
                    <div>
                        <div class="header-label">Outstanding Actions</div>
                        <div class="header-value highlight">{{ $pending }} Pending</div>
                    </div>
                </div>
            </div>
        </div>

        @if(request('q'))
            <div class="search-caption">
                Showing results for: <b>{{ request('q') }}</b>
            </div>
        @endif

        <div class="cards">
            <div class="card">
                <span class="card-label">Total Reports</span>
                <span class="card-value">{{ $totalReports }}</span>
            </div>
            <div class="card">
                <span class="card-label">Pending Reports</span>
                <span class="card-value card-value-warning">{{ $pending }}</span>
            </div>
            <div class="card">
                <span class="card-label">Accepted Reports</span>
                <span class="card-value">{{ $accepted }}</span>
            </div>
            <div class="card">
                <span class="card-label">Declined Reports</span>
                <span class="card-value">{{ $declined }}</span>
            </div>
        </div>

        <div class="panel-grid">
            <section class="panel">
                <div class="panel-head">
                    <h2>Reports by Department</h2>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Total Reports</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departmentStats ?? [] as $row)
                                <tr>
                                    <td>{{ $row->department ?? 'N/A' }}</td>
                                    <td>{{ $row->total ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align:center;">No department data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Recent Reports</h2>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Department</th>
                                <th>Calamity</th>
                                <th>Status</th>
                                <th>Date Reported</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReports ?? [] as $report)
                                <tr>
                                    <td>{{ $report->user->name ?? 'N/A' }}</td>
                                    <td>{{ $report->department ?? 'N/A' }}</td>
                                    <td>{{ $report->calamity ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($report->status ?? 'pending') }}</td>
                                    <td>{{ optional($report->created_at)->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;">No recent reports found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection