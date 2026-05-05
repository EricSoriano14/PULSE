<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>

    {{-- Global UI theme (layout + shared components) --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ @filemtime(public_path('css/dashboard.css')) }}">

    @yield('head')

    <style>
        /* Modern Pagination Styles - Global */
        .modern-pagination {
            margin-top: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .pagination-list {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-item {
            margin: 0;
        }

        .pagination-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            box-sizing: border-box;
        }

        .pagination-link:hover:not(.disabled):not(.pagination-dots) {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #111827;
        }

        .pagination-item.active .pagination-link {
            background: #2f6f3a;
            border-color: #24522b;
            color: #fff;
            font-weight: 600;
        }

        .pagination-item.disabled .pagination-link {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f9fafb;
            color: #9ca3af;
        }

        .pagination-link-prev,
        .pagination-link-next {
            padding: 0 10px;
        }

        .pagination-dots {
            border: none;
            background: transparent;
            cursor: default;
            color: #9ca3af;
        }

        .pagination-dots:hover {
            background: transparent;
            border: none;
        }

        @media (max-width: 576px) {
            .modern-pagination {
                margin-top: 18px;
            }

            .pagination-list {
                gap: 6px;
            }

            .pagination-link {
                min-width: 36px;
                height: 36px;
                font-size: 13px;
                padding: 0 10px;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    @php
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));
        $isAdmin = $role === 'admin';
        $isStaff = in_array($role, ['css', 'faculty', 'co_css'], true);
        $sectionLabel = $isAdmin ? 'Administrator' : ($isStaff ? 'Staff' : 'User');

        $notificationUnreadCount = 0;

        if (auth()->check()) {
            $notificationUnreadCount = \App\Models\Notification::query()
                ->where('user_id', auth()->id())
                ->whereNull('read_at')
                ->count();
        }
    @endphp

    {{-- Sidebar selection stays identical (role-based) --}}
    @if($isAdmin)
        @include('partials.admin-sidebar')
    @else
        @include('partials.staff-sidebar')
    @endif

    <div class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <div class="pulse-badge">P.U.L.S.E.</div>
            </div>

            <div class="topbar-right">
                <div class="user-pill">
                    <span class="user-name">{{ $sectionLabel }}</span>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="content-inner">
                @yield('content')
            </div>
        </main>
    </div>
</div>
</body>
</html>