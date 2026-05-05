@extends('layouts.admin')

@section('title', 'Announcements')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/announcements.css') }}?v={{ @filemtime(public_path('css/announcements.css')) }}">
@endsection

@section('content')
    <div class="ann-page">
        <div class="ann-page-head">
            <div>
                <h1 class="ann-title">Announcements</h1>
                <p class="ann-subtitle">Post announcements with an optional image and manage recent posts.</p>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="ann-alert ann-alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ann-alert ann-alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="ann-alert ann-alert-warn">
                <div class="ann-alert-title">Please fix the following:</div>
                <ul class="ann-alert-list">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Create --}}
        <div class="ann-card">
            <div class="ann-card-head">
                <h2 class="ann-card-title">Create New Announcement</h2>
                <p class="ann-card-subtitle">This will appear in the student app announcements/inbox.</p>
            </div>

            <form id="announcementForm" method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="ann-form-grid">
                    <div class="ann-form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="ECOAST" {{ old('department') === 'ECOAST' ? 'selected' : '' }}>ECOAST</option>
                            <option value="PBS" {{ old('department') === 'PBS' ? 'selected' : '' }}>PBS</option>
                            <option value="PUMMA" {{ old('department') === 'PUMMA' ? 'selected' : '' }}>PUMMA</option>
                            <option value="RPSEA" {{ old('department') === 'RPSEA' ? 'selected' : '' }}>RPSEA</option>
                            <option value="SOC" {{ old('department') === 'SOC' ? 'selected' : '' }}>SOC</option>
                            <option value="CBIHS" {{ old('department') === 'CBIHS' ? 'selected' : '' }}>CBIHS</option>
                        </select>
                    </div>

                    <div class="ann-form-group">
                        <label for="image">Image (optional)</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Max 5MB. JPG/PNG/WebP allowed.</small>
                    </div>

                    <div class="ann-form-group ann-form-group-full">
                        <label for="description">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            required
                            placeholder="Enter announcement description...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="ann-form-actions">
                    <button type="submit" class="ann-btn ann-btn-primary">Post</button>
                    <button type="button" class="ann-btn ann-btn-ghost" id="btnCancel">Cancel</button>
                </div>
            </form>
        </div>

        {{-- List --}}
        <div class="ann-list">
            <div class="ann-list-head">
                <h2 class="ann-list-title">Recent Announcements</h2>
                <div class="ann-list-meta">
                    @isset($announcements)
                        <span class="ann-muted">{{ method_exists($announcements, 'total') ? $announcements->total().' total' : '' }}</span>
                    @endisset
                </div>
            </div>

            @php
                // Controller passes $announcements paginator.
                $items = $announcements ?? collect();
            @endphp

            @if ($items && count($items))
                @foreach ($items as $announcement)
                    @php
                        // Backend stores: image_url (already /storage/...) and/or image_path (announcements/xxx.jpg)
                        $rawUrl = $announcement->image_url ?? null;
                        $rawPath = $announcement->image_path ?? null;

                        $imageUrl = null;

                        if (!empty($rawUrl)) {
                            $imageUrl = trim((string)$rawUrl);
                        } elseif (!empty($rawPath)) {
                            $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($rawPath);
                        }

                        $dept = $announcement->department ?? '-';
                        $desc = $announcement->description ?? '';
                        $created = $announcement->created_at ?? null;

                        $status = strtolower((string)($announcement->status ?? 'active'));
                        $isCanceled = $status === 'canceled';
                    @endphp

                    <div class="ann-post {{ $isCanceled ? 'ann-post-canceled' : '' }}">
                        <div class="ann-post-top">
                            <div class="ann-post-badges">
                                <span class="ann-pill">{{ $dept }}</span>

                                @if($isCanceled)
                                    <span class="ann-pill ann-pill-danger">Canceled</span>
                                @else
                                    <span class="ann-pill ann-pill-success">Active</span>
                                @endif
                            </div>

                            <div class="ann-post-actions">
                                @if(!$isCanceled)
                                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ann-btn ann-btn-danger"
                                            onclick="return confirm('Cancel this announcement?');">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if($created)
                            <div class="ann-date">{{ $created->toDayDateTimeString() }}</div>
                        @endif

                        @if($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                class="ann-image"
                                alt="Announcement image"
                                loading="lazy"
                                onerror="this.style.display='none';"
                            >
                        @endif

                        <div class="ann-desc" style="white-space: pre-wrap;">{{ $desc }}</div>
                    </div>
                @endforeach

                @if (method_exists($items, 'links'))
                    <div class="ann-pagination">
                        {{ $items->links() }}
                    </div>
                @endif
            @else
                <div class="ann-empty">
                    No announcements yet.
                </div>
            @endif
        </div>
    </div>

    <script>
        // Cancel button resets this form only (UI safe)
        const form = document.getElementById('announcementForm');
        const btnCancel = document.getElementById('btnCancel');

        btnCancel.addEventListener('click', function () {
            form.reset();
        });
    </script>
@endsection
