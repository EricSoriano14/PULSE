@php
    $role = strtolower(trim((string) (auth()->user()->role ?? '')));
    $email = auth()->user()->email ?? '';
    $roleLabel = strtoupper($role ?: 'UNKNOWN');

    $isAdmin = $role === 'admin';
    $isStaff = in_array($role, ['css', 'faculty'], true);

    $sectionLabel = $isAdmin ? 'ADMIN' : ($isStaff ? 'STAFF' : 'UNKNOWN');
@endphp

<aside class="sidebar">
    <div class="sidebar-header">
        <h2 class="sidebar-title">{{ $sectionLabel }}</h2>

        @if($email)
            <div class="sidebar-meta">
                <div class="sidebar-email">{{ $email }}</div>
                <div class="sidebar-role">Role: {{ $roleLabel }}</div>
            </div>
        @else
            <div class="sidebar-meta sidebar-meta-single">
                <div class="sidebar-role">Role: {{ $roleLabel }}</div>
            </div>
        @endif

        <div class="sidebar-divider"></div>
    </div>

    <nav class="sidebar-nav">
        {{-- ADMIN LINKS --}}
        @if($isAdmin)
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Dashboard</span>
            </a>

            <a href="{{ route('receive-report') }}" class="sidebar-link {{ request()->routeIs('receive-report*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Receive Report</span>
            </a>

            <a href="{{ route('records') }}" class="sidebar-link {{ request()->routeIs('records*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Records</span>
            </a>

            <a href="{{ route('announcements.index') }}" class="sidebar-link {{ request()->routeIs('announcements*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Announcements</span>
            </a>

            <a href="{{ route('admin.chatbot.index') }}" class="sidebar-link {{ request()->routeIs('admin.chatbot*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Chatbot Scripts</span>
            </a>

            <a href="{{ route('manage') }}" class="sidebar-link {{ request()->routeIs('manage*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Manage</span>
            </a>

            <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ request()->routeIs('admin.staff*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Manage Staff</span>
            </a>

            <a href="{{ route('students-status') }}" class="sidebar-link {{ request()->routeIs('students-status*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Students Status</span>
            </a>

            <a href="{{ route('staff-safety-status.index') }}" class="sidebar-link {{ request()->routeIs('staff-safety-status*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Staff Safety Status</span>
            </a>

            <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Settings</span>
            </a>

        {{-- STAFF LINKS --}}
        @elseif($isStaff)
            <a href="{{ route('receive-report') }}" class="sidebar-link {{ request()->routeIs('receive-report*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Receive Report</span>
            </a>

            <a href="{{ route('records') }}" class="sidebar-link {{ request()->routeIs('records*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Records</span>
            </a>

            <a href="{{ route('students-status') }}" class="sidebar-link {{ request()->routeIs('students-status*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Students Status</span>
            </a>

            <a href="{{ route('safety-status') }}" class="sidebar-link {{ request()->routeIs('safety-status*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">My Safety Status</span>
            </a>

            <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Settings</span>
            </a>

        {{-- FALLBACK --}}
        @else
            <div class="sidebar-alert">
                Your account role is not allowed to use this panel. Please login again.
            </div>

            <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Settings</span>
            </a>
        @endif

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="sidebar-link sidebar-logout-button">
                <span class="sidebar-link-label">Logout</span>
            </button>
        </form>
    </nav>
</aside>