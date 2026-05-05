<?php

namespace App\Http\Controllers;

use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('student_id', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {

            // block inactive accounts
            if (($user->status ?? 'active') !== 'active') {
                return back()
                    ->withErrors(['username' => 'Your account has been disabled by the administrator.'])
                    ->onlyInput('username');
            }

            $role = strtolower(trim((string) ($user->role ?? '')));

            // student accounts should not use web login
            if ($role === 'student') {
                return back()
                    ->withErrors(['username' => 'Student accounts must login using the mobile app.'])
                    ->onlyInput('username');
            }

            // block unverified staff/admin accounts
            if (in_array($role, ['admin', 'css', 'faculty', 'co_css'], true) && is_null($user->email_verified_at)) {
                return back()
                    ->withErrors(['username' => 'Please verify your email before logging in.'])
                    ->onlyInput('username');
            }

            Auth::login($user);
            $request->session()->regenerate();

            if ($role === 'admin') {
                return redirect()->route('dashboard');
            }

            if (in_array($role, ['css', 'faculty', 'co_css'], true)) {
                return redirect()->route('receive-report');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['username' => 'Invalid account role. Please contact admin.'])
                ->onlyInput('username');
        }

        return back()
            ->withErrors(['username' => 'Invalid username or password.'])
            ->onlyInput('username');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => 'No account found with that email address.'])
                ->withInput();
        }

        $role = strtolower(trim((string) ($user->role ?? '')));

        if (!in_array($role, ['admin', 'css', 'faculty', 'co_css'], true)) {
            return back()
                ->withErrors(['email' => 'This password reset flow is only available for staff accounts.'])
                ->withInput();
        }

        if (($user->status ?? 'active') !== 'active') {
            return back()
                ->withErrors(['email' => 'This account is disabled. Please contact the administrator.'])
                ->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        PasswordOtp::where('email', $user->email)->delete();

        PasswordOtp::create([
            'email' => $user->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
            'verified_at' => null,
        ]);

        Mail::raw(
            "Your password reset OTP is: {$otp}\n\nThis OTP will expire in 5 minutes.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP');
            }
        );

        return redirect()
            ->route('password.verify-otp', ['email' => $user->email])
            ->with('success', 'An OTP has been sent to your email.');
    }

    public function showVerifyOtpForm(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('password.forgot');
        }

        return view('auth.verify-otp', [
            'email' => $email,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $otpRecord = PasswordOtp::where('email', $validated['email'])
            ->where('otp', $validated['otp'])
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            return back()
                ->withErrors(['otp' => 'Invalid OTP.'])
                ->withInput();
        }

        if (!is_null($otpRecord->verified_at)) {
            return back()
                ->withErrors(['otp' => 'This OTP has already been used.'])
                ->withInput();
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            return back()
                ->withErrors(['otp' => 'OTP has expired. Please request a new one.'])
                ->withInput();
        }

        $otpRecord->verified_at = now();
        $otpRecord->save();

        return redirect()
            ->route('password.reset', ['email' => $validated['email']])
            ->with('success', 'OTP verified successfully. You may now reset your password.');
    }

    public function showResetPasswordForm(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('password.forgot');
        }

        $otpRecord = PasswordOtp::where('email', $email)
            ->whereNotNull('verified_at')
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            return redirect()
                ->route('password.forgot')
                ->withErrors(['email' => 'Please verify your OTP first.']);
        }

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otpRecord = PasswordOtp::where('email', $validated['email'])
            ->whereNotNull('verified_at')
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            return redirect()
                ->route('password.forgot')
                ->withErrors(['email' => 'Please verify your OTP first.']);
        }

        if (now()->greaterThan($otpRecord->expires_at)) {
            $otpRecord->delete();

            return redirect()
                ->route('password.forgot')
                ->withErrors(['email' => 'OTP session has expired. Please request a new OTP.']);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return redirect()
                ->route('password.forgot')
                ->withErrors(['email' => 'No account found with that email address.']);
        }

        $role = strtolower(trim((string) ($user->role ?? '')));

        if (!in_array($role, ['admin', 'css', 'faculty', 'co_css'], true)) {
            return redirect()
                ->route('password.forgot')
                ->withErrors(['email' => 'This password reset flow is only available for staff accounts.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        PasswordOtp::where('email', $validated['email'])->delete();

        return redirect()
            ->route('login')
            ->with('success', 'Password reset successfully. You may now log in.');
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        if (strtolower((string) ($user->role ?? '')) === 'student') {
            abort(403, 'Student accounts do not use this verification flow.');
        }

        if (is_null($user->email_verified_at)) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            event(new Verified($user));
        }

        return redirect()
            ->route('login')
            ->with('success', 'Your email has been verified successfully. You may now log in.');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()
                ->route('login')
                ->withErrors(['username' => 'Please log in first to resend the verification email.']);
        }

        if (!is_null($user->email_verified_at)) {
            return back()->with('success', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'A new verification email has been sent.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}