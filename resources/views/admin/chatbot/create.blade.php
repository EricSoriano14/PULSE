@extends('layouts.admin')

@section('title', 'Add Chatbot Script')

@section('content')
<style>
    .chatbot-page {
        padding: 28px;
    }

    .chatbot-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
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
        background: #ffffff;
        border-radius: 22px;
        padding: 28px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        max-width: 900px;
    }

    .chatbot-form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .chatbot-field label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .chatbot-input,
    .chatbot-textarea,
    .chatbot-select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 14px;
        padding: 13px 15px;
        font-size: 14px;
        outline: none;
        background: #fff;
        transition: 0.2s ease;
        box-sizing: border-box;
    }

    .chatbot-input:focus,
    .chatbot-textarea:focus,
    .chatbot-select:focus {
        border-color: #2f7d32;
        box-shadow: 0 0 0 4px rgba(47, 125, 50, 0.12);
    }

    .chatbot-textarea {
        min-height: 180px;
        resize: vertical;
        line-height: 1.5;
    }

    .chatbot-actions {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        flex-wrap: wrap;
    }

    .chatbot-btn {
        border: none;
        border-radius: 14px;
        padding: 12px 22px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
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

    .chatbot-btn-secondary {
        background: #f3f4f6;
        color: #111827;
        border: 1px solid #d1d5db;
    }

    .chatbot-btn-secondary:hover {
        background: #e5e7eb;
    }

    .chatbot-helper {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 14px 16px;
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
    }

    .chatbot-errors {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 18px;
    }

    .chatbot-errors ul {
        margin: 0;
        padding-left: 18px;
    }
</style>

<div class="chatbot-page">
    <div class="chatbot-header">
        <div>
            <h1 class="chatbot-title">Add Chatbot Script</h1>
            <div class="chatbot-subtitle">
                Create a new keyword and reply that the student chatbot can use.
            </div>
        </div>
    </div>

    <div class="chatbot-card">
        @if ($errors->any())
            <div class="chatbot-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.chatbot.store') }}">
            @csrf

            <div class="chatbot-form-grid">
                <div class="chatbot-field">
                    <label for="keyword">Keyword</label>
                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        class="chatbot-input"
                        value="{{ old('keyword') }}"
                        placeholder="Example: report"
                        required
                    >
                </div>

                <div class="chatbot-field">
                    <label for="reply">Reply</label>
                    <textarea
                        id="reply"
                        name="reply"
                        class="chatbot-textarea"
                        placeholder="Type the chatbot response here..."
                        required
                    >{{ old('reply') }}</textarea>
                </div>

                <div class="chatbot-field" style="max-width: 240px;">
                    <label for="is_active">Status</label>
                    <select id="is_active" name="is_active" class="chatbot-select">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="chatbot-helper">
                    Tip: Use simple keywords like <strong>report</strong>, <strong>status</strong>,
                    <strong>announcement</strong>, <strong>inbox</strong>, or <strong>safety</strong>
                    so the chatbot can match student questions more easily.
                </div>

                <div class="chatbot-actions">
                    <button type="submit" class="chatbot-btn chatbot-btn-primary">
                        Save Script
                    </button>

                    <a href="{{ route('admin.chatbot.index') }}" class="chatbot-btn chatbot-btn-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection