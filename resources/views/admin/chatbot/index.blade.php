@extends('layouts.admin')

@section('title', 'Chatbot Scripts')

@section('content')
<style>
    .chatbot-page {
        padding: 28px;
    }

    .chatbot-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .chatbot-title {
        font-size: 34px;
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .chatbot-subtitle {
        margin-top: 6px;
        color: #6b7280;
        font-size: 14px;
    }

    .chatbot-card {
        background: #fff;
        border-radius: 22px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    }

    .chatbot-btn {
        border: none;
        border-radius: 14px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s ease;
    }

    .chatbot-btn-primary {
        background: #2f7d32;
        color: #fff;
    }

    .chatbot-btn-primary:hover {
        background: #256628;
    }

    .chatbot-btn-warning {
        background: #f59e0b;
        color: #fff;
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 13px;
    }

    .chatbot-btn-warning:hover {
        background: #d97706;
    }

    .chatbot-btn-danger {
        background: #dc2626;
        color: #fff;
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 13px;
    }

    .chatbot-btn-danger:hover {
        background: #b91c1c;
    }

    .chatbot-alert {
        background: #ecfdf3;
        border: 1px solid #bbf7d0;
        color: #166534;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 600;
    }

    .chatbot-table-wrap {
        overflow-x: auto;
    }

    .chatbot-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .chatbot-table th {
        text-align: left;
        background: #f9fafb;
        color: #374151;
        font-size: 13px;
        font-weight: 800;
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    .chatbot-table td {
        padding: 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
        font-size: 14px;
        color: #111827;
    }

    .chatbot-keyword {
        font-weight: 700;
        color: #2f7d32;
    }

    .chatbot-reply {
        max-width: 460px;
        line-height: 1.5;
        color: #374151;
    }

    .chatbot-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .chatbot-badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .chatbot-badge-inactive {
        background: #e5e7eb;
        color: #374151;
    }

    .chatbot-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .chatbot-empty {
        text-align: center;
        color: #6b7280;
        padding: 28px 0;
        font-weight: 600;
    }
</style>

<div class="chatbot-page">
    <div class="chatbot-header">
        <div>
            <h1 class="chatbot-title">Chatbot Scripts</h1>
            <div class="chatbot-subtitle">
                Manage the keywords and replies used by the student support chatbot.
            </div>
        </div>

        <a href="{{ route('admin.chatbot.create') }}" class="chatbot-btn chatbot-btn-primary">
            Add Script
        </a>
    </div>

    @if(session('success'))
        <div class="chatbot-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="chatbot-card">
        <div class="chatbot-table-wrap">
            <table class="chatbot-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 180px;">Keyword</th>
                        <th>Reply</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $response)
                        <tr>
                            <td>{{ $response->id }}</td>
                            <td>
                                <span class="chatbot-keyword">{{ $response->keyword }}</span>
                            </td>
                            <td>
                                <div class="chatbot-reply">{{ $response->reply }}</div>
                            </td>
                            <td>
                                @if($response->is_active)
                                    <span class="chatbot-badge chatbot-badge-active">Active</span>
                                @else
                                    <span class="chatbot-badge chatbot-badge-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="chatbot-actions">
                                    <a href="{{ route('admin.chatbot.edit', $response) }}" class="chatbot-btn chatbot-btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.chatbot.destroy', $response) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="chatbot-btn chatbot-btn-danger" onclick="return confirm('Delete this script?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="chatbot-empty">No chatbot scripts found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection