@extends('layouts.admin')

@section('title', 'Record Details')

@section('content')
    @php
        $action = $report->action;

        $locationAddress = trim((string) ($report->location_address ?? ''));
        $latitudeValue = $report->latitude;
        $longitudeValue = $report->longitude;

        $studentName = $report->user?->name ?? '-';
        $studentId = $report->user?->student_id ?? $report->id;
        $department = $report->department ?? ($report->user?->department ?? '-');
        $course = $report->user?->course ?? '-';
        $status = ucfirst($report->status ?? '-');
        $calamity = $report->calamity ?? '-';
        $description = $report->description ?? '-';
        $submittedAt = optional($report->submitted_at ?? $report->created_at)->toDayDateTimeString() ?? '-';
    @endphp

    <div class="record-details-page">
        <div class="page-actions" style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px;">
            <div>
                <h1 class="page-title" style="margin:0; font-size:36px; font-weight:800;">Record Details</h1>
                <p style="margin:6px 0 0; color:#6b7280;">View the submitted report information, location, and decisions.</p>
            </div>
            <a href="{{ route('records') }}" class="btn btn-secondary btn-reset-filter">Back</a>
        </div>

        <div class="panel detail-panel panel-spaced" style="padding:24px; border-radius:18px; background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.08); border:1px solid #e5e7eb; margin-bottom:22px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; flex-wrap:wrap;">
                <h2 style="margin:0; font-size:22px; font-weight:700;">Report Information</h2>
                <span class="status-badge status-{{ $report->status }} status-pill" style="font-size:14px; padding:8px 14px; border-radius:999px;">
                    {{ $status }}
                </span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
                <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:14px; padding:18px;">
                    <h3 style="margin:0 0 14px; font-size:16px; font-weight:700; color:#0f172a;">Student Details</h3>

                    <div style="display:grid; gap:12px;">
                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Name</div>
                            <div style="font-size:16px; color:#111827;">{{ $studentName }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">ID</div>
                            <div style="font-size:16px; color:#111827;">{{ $studentId }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Department</div>
                            <div style="font-size:16px; color:#111827;">{{ $department }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Course</div>
                            <div style="font-size:16px; color:#111827;">{{ $course }}</div>
                        </div>
                    </div>
                </div>

                <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:14px; padding:18px;">
                    <h3 style="margin:0 0 14px; font-size:16px; font-weight:700; color:#0f172a;">Report Details</h3>

                    <div style="display:grid; gap:12px;">
                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Calamity</div>
                            <div style="font-size:16px; color:#111827;">{{ $calamity }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Submitted At</div>
                            <div style="font-size:16px; color:#111827;">{{ $submittedAt }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Description</div>
                            <div style="font-size:16px; color:#111827; white-space:pre-wrap;">{{ $description }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel detail-panel panel-spaced" style="padding:24px; border-radius:18px; background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.08); border:1px solid #e5e7eb; margin-bottom:22px;">
            <h2 style="margin:0 0 18px; font-size:22px; font-weight:700;">Location Information</h2>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
                <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:14px; padding:18px;">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:8px;">Location Address</div>
                    <div style="font-size:16px; color:#111827; line-height:1.6; white-space:pre-wrap;">
                        {{ $locationAddress !== '' ? $locationAddress : 'Not provided' }}
                    </div>
                </div>

                <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:14px; padding:18px;">
                    <div style="display:grid; gap:14px;">
                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Latitude</div>
                            <div style="font-size:16px; color:#111827;">
                                {{ $latitudeValue !== null ? number_format((float) $latitudeValue, 6) : 'Not provided' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Longitude</div>
                            <div style="font-size:16px; color:#111827;">
                                {{ $longitudeValue !== null ? number_format((float) $longitudeValue, 6) : 'Not provided' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-block" style="margin-bottom:22px;">
            <h3 class="section-title" style="margin:0 0 12px; font-size:22px; font-weight:700;">Faculty Recommendation</h3>
            <div class="panel detail-panel" style="padding:24px; border-radius:18px; background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.08); border:1px solid #e5e7eb;">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:18px;">
                    <div>
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Recommended Action</div>
                        <div style="font-size:16px; color:#111827;">{{ $action?->recommended_action ? ucfirst($action->recommended_action) : '-' }}</div>
                    </div>

                    <div>
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Recommended At</div>
                        <div style="font-size:16px; color:#111827;">{{ optional($action?->recommended_at)->toDayDateTimeString() ?? '-' }}</div>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Recommendation Note</div>
                        <div style="font-size:16px; color:#111827; white-space:pre-wrap;">{{ $action?->recommended_note ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-block">
            <h3 class="section-title" style="margin:0 0 12px; font-size:22px; font-weight:700;">Admin Decision</h3>

            <div class="panel detail-panel" style="padding:24px; border-radius:18px; background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.08); border:1px solid #e5e7eb;">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:18px;">
                    <div>
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Decision Status</div>
                        <div style="font-size:16px; color:#111827;">{{ $action?->decision ? ucfirst($action->decision) : '-' }}</div>
                    </div>

                    <div>
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Decided At</div>
                        <div style="font-size:16px; color:#111827;">{{ optional($action?->decision_at)->toDayDateTimeString() ?? '-' }}</div>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Public Remark (Student)</div>
                        <div style="font-size:16px; color:#111827; white-space:pre-wrap;">{{ $action?->public_remark ?? '-' }}</div>
                    </div>

                    <div style="grid-column:1 / -1;">
                        <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Action Note</div>
                        <div style="font-size:16px; color:#111827; white-space:pre-wrap;">{{ $action?->action_taken_note ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection