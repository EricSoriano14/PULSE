<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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
                    Enter the one-time password (OTP) sent to your email
                    to continue resetting your password.
                </p>
            </div>

            <div class="login-box">
                <div class="login-header">
                    <h1>Verify OTP</h1>
                    <p>Enter the 6-digit OTP sent to your email</p>
                </div>

                <form method="POST" action="{{ route('password.verify-otp.submit') }}">
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
                        <label for="otp">OTP Code</label>
                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            placeholder="Enter 6-digit OTP"
                            value="{{ old('otp') }}"
                            maxlength="6"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-primary">Verify OTP</button>

                    <div style="margin-top: 14px; text-align: center;">
                        <a
                            href="{{ route('password.forgot') }}"
                            style="font-size: 14px; font-weight: 600; color: #2563eb; text-decoration: none;"
                        >
                            Back to Forgot Password
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>