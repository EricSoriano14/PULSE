@php
    $role = strtolower(trim((string) (auth()->user()->role ?? '')));
    $email = auth()->user()->email ?? '';
    $roleLabel = strtoupper($role ?: 'UNKNOWN');

    $isStaff = in_array($role, ['css', 'faculty', 'co_css'], true);
    $sectionLabel = $isStaff ? 'STAFF' : 'UNKNOWN';
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
        @if($isStaff)

            @if(in_array($role, ['css', 'faculty', 'co_css'], true))
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <span class="sidebar-link-label">Dashboard</span>
                </a>
            @endif

            <a href="{{ route('receive-report') }}" class="sidebar-link {{ request()->routeIs('receive-report*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Receive Report</span>
            </a>

            <a href="{{ route('notifications.index') }}"
               class="sidebar-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}"
               style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                <span style="display:flex; align-items:center; gap:10px;">
                    <span aria-hidden="true">🔔</span>
                    <span class="sidebar-link-label">Notifications</span>
                </span>

                @if(($notificationUnreadCount ?? 0) > 0)
                    <span style="
                        min-width:22px;
                        height:22px;
                        padding:0 7px;
                        border-radius:999px;
                        background:#ef4444;
                        color:#fff;
                        font-size:12px;
                        font-weight:700;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        line-height:1;
                        flex-shrink:0;
                    ">
                        {{ ($notificationUnreadCount ?? 0) > 99 ? '99+' : $notificationUnreadCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('records') }}" class="sidebar-link {{ request()->routeIs('records*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Records</span>
            </a>

            <a href="{{ route('students-status') }}" class="sidebar-link {{ request()->routeIs('students-status*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Students Status</span>
            </a>

            @if($role === 'css')
                <a href="{{ route('css.cocss.index') }}" class="sidebar-link {{ request()->routeIs('css.cocss.*') ? 'is-active' : '' }}">
                    <span class="sidebar-link-label">Manage Co-CSS</span>
                </a>
            @endif

            <a href="{{ route('safety-status') }}" class="sidebar-link {{ request()->routeIs('safety-status*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">My Safety Status</span>
            </a>

            <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings*') ? 'is-active' : '' }}">
                <span class="sidebar-link-label">Settings</span>
            </a>
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