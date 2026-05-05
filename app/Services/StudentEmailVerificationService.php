<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class StudentEmailVerificationService
{
    public function send(User $user): void
    {
        if (strtolower((string) $user->role) !== 'student') {
            return;
        }

        if (!is_null($user->email_verified_at)) {
            return;
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Store OTP in email_verification_otps table (delete old one first)
        DB::table('email_verification_otps')->where('email', $user->email)->delete();

        DB::table('email_verification_otps')->insert([
            'email'      => $user->email,
            'otp'        => Hash::make($otp),
            'created_at' => now(),
        ]);

        // Send OTP via email
        Mail::raw(
            "Hello {$user->name},\n\n"
            . "Your email verification OTP code for the Student Calamity Incident and Safety Reporting System is:\n\n"
            . "  {$otp}\n\n"
            . "Enter this code in the app to verify your account.\n\n"
            . "This code will expire in 10 minutes.\n\n"
            . "If you did not create this account, please ignore this email.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Email Verification OTP - Panpacific Student Account');
            }
        );
    }
}