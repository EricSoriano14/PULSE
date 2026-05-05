@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <style>
        .notifications-page {
            max-width: 1100px;
            margin: 0 auto;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .notifications-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #111827;
        }

        .notifications-header p {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .notifications-flash {
            margin-bottom: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #dcfce7;
            color: #166534;
            font-weight: 600;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .notification-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease;
        }

        .notification-card.is-unread {
            background: #f8fafc;
            border-color: #dbe4ee;
        }

        .notification-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
        }

        .notification-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }

        .notification-main {
            flex: 1 1 620px;
            min-width: 280px;
        }

        .notification-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .notification-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #ef4444;
            flex-shrink: 0;
        }

        .notification-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #111827;
        }

        .notification-message {
            margin: 0 0 10px;
            color: #374151;
            line-height: 1.65;
            font-size: 14px;
            word-break: break-word;
        }

        .notification-time {
            color: #9ca3af;
            font-size: 12.5px;
            font-weight: 600;
        }

        .notification-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            flex: 0 0 auto;
            min-width: 180px;
            flex-wrap: wrap;
        }

        .notification-empty {
            padding: 36px 20px;
            text-align: center;
            color: #6b7280;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
        }

        @media (max-width: 768px) {
            .notifications-header {
                align-items: stretch;
            }

            .notification-row {
                flex-direction: column;
            }

            .notification-actions {
                width: 100%;
                justify-content: flex-start;
                min-width: 0;
            }
        }
    </style>

    <div class="notifications-page">
        <div class="notifications-header">
            <div>
                <h1>Notifications</h1>
                <p>View your recent notification alerts.</p>
            </div>

            @if(($unreadCount ?? 0) > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="notifications-flash">
                {{ session('success') }}
            </div>
        @endif

        <div class="notifications-list">
            @forelse($notifications as $notification)
                <div class="notification-card {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                    <div class="notification-row">
                        <div class="notification-main">
                            <div class="notification-title-row">
                                @if(is_null($notification->read_at))
                                    <span class="notification-dot"></span>
                                @endif

                                <h3 class="notification-title">
                                    {{ $notification->title }}
                                </h3>
                            </div>

                            <p class="notification-message">
                                {{ $notification->message }}
                            </p>

                            <div class="notification-time">
                                {{ optional($notification->created_at)->diffForHumans() }}
                            </div>
                        </div>

                        <div class="notification-actions">
                            @if(!$notification->read_at)
                                <form action="{{ route('notifications.read', $notification) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">
                                        Mark as read
                                    </button>
                                </form>
                            @endif

                            @if(!empty($notification->target_url))
                                <a href="{{ $notification->target_url }}" class="btn btn-primary">
                                    Open
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="notification-empty">
                    No notifications found.
                </div>
            @endforelse
        </div>

        @if(method_exists($notifications, 'links'))
            <div style="margin-top: 20px;">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection