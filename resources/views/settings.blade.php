@extends('layouts.admin')

@section('title', 'Settings')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ @filemtime(public_path('css/settings.css')) }}">
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1>Settings</h1>
            <p class="subtext">Update your profile, change your password, and manage session actions.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="panel" style="margin-bottom: 12px; color: #166534; background: #ecfdf3;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="panel" style="margin-bottom: 12px; color: #b91c1c; background: #fef2f2;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="panel-grid">
        <section class="panel">
            <div class="panel-head">
                <h3>Profile</h3>
            </div>

            <form class="form-grid" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                @csrf

                <label class="form-field" style="grid-column: 1 / -1;">
                    <span>Avatar</span>
                    <div class="avatar-row">
                        <div style="width:72px;height:72px;border-radius:12px;overflow:hidden;background:#f3f4f6;">
                            @if($user->avatar_path)
                                <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-weight:700;">IMG</div>
                            @endif
                        </div>
                        <div class="file-picker">
                            <input id="avatar" class="file-input" type="file" name="avatar" accept="image/*">
                            <label for="avatar" class="btn btn-secondary">Choose profile</label>
                            <span id="avatar-filename" class="file-name">No file selected</span>
                        </div>
                    </div>
                </label>

                <label class="form-field">
                    <span>Full name</span>
                    <input type="text" name="name" placeholder="Your name" value="{{ old('name', $user->name) }}" required>
                </label>

                <label class="form-field">
                    <span>Email</span>
                    <input type="email" name="email" placeholder="you@example.com" value="{{ old('email', $user->email) }}" required>
                </label>

                <label class="form-field">
                    <span>Address</span>
                    <input type="text" name="address" placeholder="Address" value="{{ old('address', $user->address) }}">
                </label>

                <label class="form-field" style="grid-column: 1 / -1;">
                    <span>Info</span>
                    <textarea name="info" rows="4" placeholder="Short info/bio">{{ old('info', $user->info) }}</textarea>
                </label>

                <div class="form-actions" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary">Save profile</button>
                </div>
            </form>

            <div class="panel-divider"></div>

            <div class="panel-head" style="padding: 0; margin-bottom: 12px;">
                <h3 style="margin: 0;">Change Password</h3>
            </div>
            <p class="subtext" style="margin-bottom: 12px;">Update your account password securely.</p>

            <form class="form-grid" method="POST" action="{{ route('settings.change-password') }}">
                @csrf

                <label class="form-field">
                    <span>Current Password</span>
                    <input type="password" name="current_password" placeholder="Enter current password" required>
                </label>

                <label class="form-field">
                    <span>New Password</span>
                    <input type="password" name="password" placeholder="Enter new password" required>
                </label>

                <label class="form-field">
                    <span>Confirm New Password</span>
                    <input type="password" name="password_confirmation" placeholder="Confirm new password" required>
                </label>

                <div class="form-actions" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>

            <div class="panel-divider"></div>

            <div class="danger-zone">
                <div class="panel-head" style="padding: 0; margin-bottom: 8px;">
                    <h3 style="margin: 0;">Logout</h3>
                </div>
                <p class="subtext">Sign out of the admin dashboard.</p>
                <form method="POST" action="{{ route('logout') }}" class="form-actions">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const input = document.getElementById('avatar');
            const nameEl = document.getElementById('avatar-filename');
            if (!input || !nameEl) return;

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                nameEl.textContent = file ? file.name : 'No file selected';
            });
        })();
    </script>
@endsection