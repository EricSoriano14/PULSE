<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="login-page">
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>
        <div class="bg-glow glow-1"></div>
        <div class="bg-glow glow-2"></div>

        <div class="login-wrapper">
            <div class="login-brand">
                <div class="brand-badge">Panpacific University North Philippines</div>
                <h2>Student Calamity Incident and Safety Reporting System</h2>
                <p>
                    Create a new password for your staff account.
                    Make sure it is secure and easy for you to remember.
                </p>
            </div>

            <div class="login-box">
                <div class="login-header">
                    <h1>Reset Password</h1>
                    <p>Enter your new password below</p>
                </div>

                <form method="POST" action="{{ route('password.reset.submit') }}">
                    @csrf

                    @if (session('success'))
                        <div class="alert-success" style="margin-bottom: 14px; padding: 12px 14px; border-radius: 12px; background: #ecfdf3; color: #166534; font-weight: 600;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <input type="hidden" name="email" value="{{ old('email', $email ?? request('email')) }}">

                    <div class="input-group">
                        <label for="password">New Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter new password"
                            required
                        >
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Confirm new password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-primary">Reset Password</button>

                    <div style="margin-top: 14px; text-align: center;">
                        <a
                            href="{{ route('login') }}"
                            style="font-size: 14px; font-weight: 600; color: #2563eb; text-decoration: none;"
                        >
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>