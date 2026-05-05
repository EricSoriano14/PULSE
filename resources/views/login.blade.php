<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Login</title>
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
          PULSE, monitoring student submissions,
          and reviewing calamity-related records.
        </p>
      </div>

      <div class="login-box">
        <div class="login-header">
          <h1>Staff Login</h1>
          <p>Sign in to continue to the dashboard</p>
        </div>

        <form method="POST" action="{{ route('login.perform') }}">
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

          <div class="input-group">
            <label for="username">Username or Email</label>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Enter your username or email"
              value="{{ old('username') }}"
              required
            >
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              required
            >
          </div>

          <div style="display:flex; justify-content:flex-end; margin-top:-4px; margin-bottom:14px;">
            <a
              href="{{ route('password.forgot') }}"
              style="font-size:14px; font-weight:600; color:#2563eb; text-decoration:none;"
            >
              Forgot Password?
            </a>
          </div>

          <button type="submit" class="btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>