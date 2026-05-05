@extends('layouts.admin')

@section('title', 'My Safety Status')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/safety-status.css') }}?v={{ @filemtime(public_path('css/safety-status.css')) }}">
@endsection

@section('content')
    <div class="safety-status-page">
        <div class="page-header" style="margin-bottom: 24px;">
            <div>
                <h1 style="margin: 0;">My Safety Status</h1>
                <p class="subtext">Mark yourself as safe or at risk during calamities.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="panel success-message" style="margin-bottom: 24px;">
                {{ session('success') }}
            </div>
        @endif

        <div class="safety-status-card">
            <div class="current-status">
                <div class="status-header">
                    <h2>Current Status</h2>
                    @php
                        $status = $user->safety_status ?? 'safe';
                        $label = $status === 'safe' ? 'Safe' : 'At Risk';
                    @endphp
                    <span class="safety-badge safety-{{ $status }}">
                        {{ $label }}
                    </span>
                </div>

                <p class="status-description">
                    @if(($user->safety_status ?? 'safe') === 'safe')
                        You have marked yourself as <strong>Safe</strong>. This means you are currently safe and not in immediate danger.
                    @else
                        You have marked yourself as <strong>At Risk</strong>. Please update your status to "Safe" once you are in a safe location.
                    @endif
                </p>
            </div>

            <div class="update-status">
                <h3>Update Your Status</h3>

                {{-- ✅ IMPORTANT: route accepts POST, so remove PATCH method spoofing --}}
                <form method="POST" action="{{ route('safety-status.update') }}" class="status-form">
                    @csrf

                    <div class="status-options">
                        <label class="status-option">
                            <input type="radio"
                                   name="safety_status"
                                   value="safe"
                                   {{ ($user->safety_status ?? 'safe') === 'safe' ? 'checked' : '' }}>
                            <div class="option-content">
                                <div class="option-icon safe-icon">✓</div>
                                <div>
                                    <div class="option-title">I'm Safe</div>
                                    <div class="option-description">Mark yourself as safe if you are not in danger</div>
                                </div>
                            </div>
                        </label>

                        <label class="status-option">
                            <input type="radio"
                                   name="safety_status"
                                   value="at_risk"
                                   {{ ($user->safety_status ?? 'safe') === 'at_risk' ? 'checked' : '' }}>
                            <div class="option-content">
                                <div class="option-icon risk-icon">!</div>
                                <div>
                                    <div class="option-title">I'm At Risk</div>
                                    <div class="option-description">Mark yourself as at risk if you need assistance</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
@endsection
