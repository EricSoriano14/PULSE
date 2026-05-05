@extends('layouts.admin')

@section('title', 'Report Details')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/receive-report.css') }}?v={{ @filemtime(public_path('css/receive-report.css')) }}">
@endsection

@section('content')
    @php
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));
        $canDecide = in_array($role, ['admin','css','faculty','co_css'], true);
        $canRecommend = in_array($role, ['css','faculty','co_css'], true);
        $canAssignCoCss = in_array($role, ['admin', 'css'], true);

        $studentName =
            $report->user?->full_name
            ?? $report->user?->name
            ?? trim(($report->student?->first_name ?? '') . ' ' . ($report->student?->last_name ?? ''));
        $studentName = trim((string) $studentName) !== '' ? $studentName : '-';

        $studentEmail = $report->user?->email ?? $report->student?->email ?? '-';

        $deptName =
            $report->department
            ?? $report->student?->department?->name
            ?? $report->user?->department
            ?? '-';

        $courseName =
            $report->student?->course?->name
            ?? $report->user?->course
            ?? '-';

        $assignedFacultyName = $report->assignedFaculty?->name
            ?? $report->assignedFaculty?->full_name
            ?? '-';

        $assignedCoCssName = $report->assignedCoCss?->name
            ?? $report->assignedCoCss?->full_name
            ?? '-';

        $imgs = $report->images ?? collect();
        $action = $report->action;
        $submittedAt = optional($report->submitted_at ?? $report->created_at)->toDayDateTimeString();

        $locationAddress = trim((string) ($report->location_address ?? ''));
        $latitudeValue = $report->latitude;
        $longitudeValue = $report->longitude;
    @endphp

    <div class="report-details-page">
        <div class="page-actions">
            <div>
                <h1 class="page-title">Report Details</h1>
                <p class="subtext page-subtext">Review report information, attachments, and actions.</p>
            </div>
            <a href="{{ route('receive-report') }}" class="btn btn-secondary btn-reset-filter">Back</a>
        </div>

        @if (session('success'))
            <div class="flash-message flash-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message flash-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flash-message flash-warning">
                <div class="flash-title">Please fix the following:</div>
                <ul class="flash-list">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel panel-spaced">
            <div class="detail-grid">
                <div class="detail-label">ID</div>
                <div class="detail-value">{{ $report->id }}</div>

                <div class="detail-label">Student</div>
                <div class="detail-value">{{ $studentName }}</div>

                <div class="detail-label">Email</div>
                <div class="detail-value">{{ $studentEmail }}</div>

                <div class="detail-label">Calamity</div>
                <div class="detail-value">{{ $report->calamity }}</div>

                <div class="detail-label">Department</div>
                <div class="detail-value">{{ $deptName }}</div>

                <div class="detail-label">Course</div>
                <div class="detail-value">{{ $courseName }}</div>

                <div class="detail-label">Assigned Faculty</div>
                <div class="detail-value">{{ $assignedFacultyName }}</div>

                <div class="detail-label">Assigned Co-CSS</div>
                <div class="detail-value">{{ $assignedCoCssName }}</div>

                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge status-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
                </div>

                <div class="detail-label">Submitted at</div>
                <div class="detail-value">{{ $submittedAt }}</div>

                <div class="detail-label">Location Address</div>
                <div class="detail-value detail-prewrap">
                    {{ $locationAddress !== '' ? $locationAddress : 'Not provided' }}
                </div>

                <div class="detail-label">Latitude</div>
                <div class="detail-value">
                    {{ $latitudeValue !== null ? number_format((float) $latitudeValue, 6) : 'Not provided' }}
                </div>

                <div class="detail-label">Longitude</div>
                <div class="detail-value">
                    {{ $longitudeValue !== null ? number_format((float) $longitudeValue, 6) : 'Not provided' }}
                </div>

                <div class="detail-label">Description</div>
                <div class="detail-value detail-prewrap">{{ $report->description ?? '-' }}</div>

                <div class="detail-label">Attachments</div>
                <div class="detail-value">
                    @if($imgs->count())
                        <div class="attachments-grid">
                            @foreach($imgs as $img)
                                @php
                                    $url = null;
                                    if (!empty($img->path)) {
                                        $url = \Illuminate\Support\Facades\Storage::disk('public')->url($img->path);
                                    } elseif (!empty($img->image_url)) {
                                        $url = $img->image_url;
                                    }
                                @endphp

                                @if($url)
                                    <a href="{{ $url }}" target="_blank" class="attachment-link">
                                        <img src="{{ $url }}" class="attachment-thumb" alt="Attachment">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        @if ($canAssignCoCss)
            <div class="panel panel-spaced">
                <div class="panel-head-row">
                    <h3>Assign Co-CSS</h3>
                    <div class="panel-note">
                        {{ $report->assigned_co_css_id ? 'Reassignment available' : 'No Co-CSS assigned yet' }}
                    </div>
                </div>

                @if(isset($coCssUsers) && $coCssUsers->count())
                    <form method="POST" action="{{ route('receive-report.assign-co-css', $report) }}" class="inline-form-reset">
                        @csrf

                        <div class="form-grid">
                            <div class="detail-label">Select Co-CSS</div>
                            <div>
                                <select name="co_css_id" required>
                                    <option value="">-- Select Co-CSS --</option>
                                    @foreach($coCssUsers as $coCss)
                                        <option value="{{ $coCss->id }}"
                                            {{ (string) old('co_css_id', $report->assigned_co_css_id) === (string) $coCss->id ? 'selected' : '' }}>
                                            {{ $coCss->name }}{{ !empty($coCss->department) ? ' (' . $coCss->department . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('co_css_id')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div></div>
                            <div class="form-actions-inline">
                                <button type="submit" class="btn btn-secondary">
                                    {{ $report->assigned_co_css_id ? 'Reassign Co-CSS' : 'Assign Co-CSS' }}
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="panel-note">No Co-CSS accounts available yet.</div>
                @endif
            </div>
        @endif

        <div class="panel panel-spaced">
            <div class="panel-head-row">
                <h3>Faculty Recommendation</h3>
                <div class="panel-note">
                    @if($report->status === 'pending')
                        Open (Pending)
                    @else
                        Closed ({{ ucfirst($report->status) }})
                    @endif
                </div>
            </div>

            @if ($action && ($action->recommended_action || $action->recommended_note || $action->recommended_at))
                <div class="recommendation-box">
                    <div class="detail-grid">
                        <div class="detail-label">Recommended Action</div>
                        <div class="detail-value detail-strong">
                            {{ $action->recommended_action ? strtoupper($action->recommended_action) : '-' }}
                        </div>

                        <div class="detail-label">Recommendation Note</div>
                        <div class="detail-value detail-prewrap">{{ $action->recommended_note ?? '-' }}</div>

                        <div class="detail-label">Recommended At</div>
                        <div class="detail-value">{{ $action->recommended_at ? $action->recommended_at->toDayDateTimeString() : '-' }}</div>
                    </div>
                </div>
            @else
                <div class="panel-note panel-note-spaced">No recommendation submitted yet.</div>
            @endif

            @if ($report->status === 'pending' && $canRecommend)
                <form method="POST" action="{{ route('receive-report.recommend', $report) }}" class="inline-form-reset">
                    @csrf

                    <div class="form-grid">
                        <div class="detail-label">Recommended Action</div>
                        <div>
                            <select name="recommended_action" id="recommended_action_main" required class="recommend-action-select">
                                <option value="">-- Select --</option>
                                <option value="accept" {{ old('recommended_action', $action?->recommended_action) === 'accept' ? 'selected' : '' }}>Accept</option>
                                <option value="decline" {{ old('recommended_action', $action?->recommended_action) === 'decline' ? 'selected' : '' }}>Decline</option>
                            </select>
                            @error('recommended_action')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="detail-label">Recommendation Note (optional)</div>
                        <div>
                            <textarea name="recommended_note" id="recommended_note_main" rows="4">{{ old('recommended_note', $action?->recommended_note) }}</textarea>
                            @error('recommended_note')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div></div>
                        <div class="form-actions-inline">
                            <button type="submit" class="btn btn-secondary">Submit Recommendation</button>
                        </div>
                    </div>
                </form>
            @elseif ($report->status !== 'pending')
                <div class="panel-note">Recommendation is disabled because the report is no longer pending.</div>
            @else
                <div class="panel-note">Recommendation is available only for CSS/Faculty/Co-CSS while the report is pending.</div>
            @endif
        </div>

        <div class="panel">
            <div class="panel-head-row">
                <h3>Decision</h3>
                <div class="panel-note">
                    @if($report->status === 'pending')
                        Open (Pending)
                    @else
                        Closed ({{ ucfirst($report->status) }})
                    @endif
                </div>
            </div>

            @if ($canDecide)
                @if($report->status === 'pending')
                    <div class="form-actions-inline">
                        <form method="POST" action="{{ route('receive-report.decide', $report) }}" class="decision-form inline-form-reset">
                            @csrf
                            <input type="hidden" name="decision" value="accepted">
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="status" value="accepted">
                            <input type="hidden" name="result" value="accepted">
                            <input type="hidden" name="recommended_action" class="recommended_action_sync" value="{{ old('recommended_action', $action?->recommended_action) }}">
                            <input type="hidden" name="recommended_note" class="recommended_note_sync" value="{{ old('recommended_note', $action?->recommended_note) }}">
                            <button type="submit" class="btn btn-primary">Accept</button>
                        </form>

                        <form method="POST" action="{{ route('receive-report.decide', $report) }}" class="decision-form inline-form-reset">
                            @csrf
                            <input type="hidden" name="decision" value="declined">
                            <input type="hidden" name="action" value="decline">
                            <input type="hidden" name="status" value="declined">
                            <input type="hidden" name="result" value="declined">
                            <input type="hidden" name="recommended_action" class="recommended_action_sync" value="{{ old('recommended_action', $action?->recommended_action) }}">
                            <input type="hidden" name="recommended_note" class="recommended_note_sync" value="{{ old('recommended_note', $action?->recommended_note) }}">
                            <button type="submit" class="btn btn-danger">Decline</button>
                        </form>

                        <form method="POST" action="{{ route('receive-report.action-taken', $report) }}" class="inline-form-reset">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Mark Action Taken</button>
                        </form>
                    </div>
                @else
                    <div class="panel-note">Decision is disabled because the report is no longer pending.</div>
                @endif
            @else
                <div class="panel-note">You do not have permission to decide on this report.</div>
            @endif
        </div>
    </div>

    @if ($report->status === 'pending')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const actionInput = document.getElementById('recommended_action_main');
                const noteInput = document.getElementById('recommended_note_main');
                const syncedActionInputs = document.querySelectorAll('.recommended_action_sync');
                const syncedNoteInputs = document.querySelectorAll('.recommended_note_sync');
                const decisionForms = document.querySelectorAll('.decision-form');

                function syncRecommendationFields() {
                    const actionValue = actionInput ? actionInput.value : '';
                    const noteValue = noteInput ? noteInput.value : '';

                    syncedActionInputs.forEach(function (input) {
                        input.value = actionValue;
                    });

                    syncedNoteInputs.forEach(function (input) {
                        input.value = noteValue;
                    });
                }

                if (actionInput) {
                    actionInput.addEventListener('change', syncRecommendationFields);
                    actionInput.addEventListener('input', syncRecommendationFields);
                }

                if (noteInput) {
                    noteInput.addEventListener('input', syncRecommendationFields);
                    noteInput.addEventListener('change', syncRecommendationFields);
                }

                decisionForms.forEach(function (form) {
                    form.addEventListener('submit', function () {
                        syncRecommendationFields();
                    });
                });

                syncRecommendationFields();
            });
        </script>
    @endif
@endsection