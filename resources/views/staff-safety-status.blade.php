@extends('layouts.admin')

@section('content')
<div class="content">
    <h1 style="margin-bottom: 6px;">Staff Safety Status</h1>
    <p style="margin-top: 0; color: #666;">
        CSS + Faculty safety monitoring (Admin only).
        <span style="display:inline-block; margin-left:10px; font-size: 12px; color:#888;">
            Logged in as: {{ auth()->user()->email ?? 'unknown' }} ({{ strtoupper(auth()->user()->role ?? 'unknown') }})
        </span>
    </p>

    @php
        $viewerRole = strtolower(trim((string) (auth()->user()->role ?? '')));
    @endphp

    @if($viewerRole !== 'admin')
        <div style="margin-top: 10px; padding: 10px; border: 1px solid #fed7d7; background: #fff5f5; color: #742a2a;">
            Unauthorized. Please login as Admin.
        </div>
    @else

        @if(session('success'))
            <div style="margin-top: 10px; padding: 10px; border: 1px solid #c6f6d5; background: #f0fff4; color: #22543d;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-top: 10px; padding: 10px; border: 1px solid #fed7d7; background: #fff5f5; color: #742a2a;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="table-wrap" style="margin-top: 14px;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding: 10px; border-bottom: 1px solid #ddd;">Role</th>
                        <th style="text-align:left; padding: 10px; border-bottom: 1px solid #ddd;">Name</th>
                        <th style="text-align:left; padding: 10px; border-bottom: 1px solid #ddd;">Email</th>
                        <th style="text-align:left; padding: 10px; border-bottom: 1px solid #ddd;">Safety Status</th>
                        <th style="text-align:left; padding: 10px; border-bottom: 1px solid #ddd;">Last Updated</th>
                        <th style="text-align:left; padding: 10px; border-bottom: 1px solid #ddd;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $u)
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ strtoupper($u->role) }}</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $u->full_name ?? '-' }}</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $u->email ?? '-' }}</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                <strong>{{ strtoupper($u->safety_status ?? 'unknown') }}</strong>
                            </td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                {{ optional($u->updated_at)->format('Y-m-d H:i') ?? '-' }}
                            </td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                <form method="POST"
                                      action="{{ route('staff-safety-status.update', $u->id) }}"
                                      style="display:flex; gap:8px; align-items:center;">
                                    @csrf
                                    <select name="safety_status" required>
                                        <option value="safe" {{ ($u->safety_status ?? '') === 'safe' ? 'selected' : '' }}>SAFE</option>
                                        <option value="at_risk" {{ ($u->safety_status ?? '') === 'at_risk' ? 'selected' : '' }}>AT RISK</option>
                                    </select>
                                    <button type="submit" style="padding: 6px 10px; cursor: pointer;">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 14px;">No staff records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @endif
</div>
@endsection
