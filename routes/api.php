<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\Student\StudentAnnouncementController;
use App\Http\Controllers\Api\Student\StudentNotificationController;
use App\Http\Controllers\Api\Student\StudentReportController;
use App\Http\Controllers\Api\Student\StudentSafetyController;
use App\Http\Controllers\Api\Student\ChatbotController;

Route::prefix('student')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Student Auth Routes
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [StudentAuthController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | Public Student Registration + OTP Email Verification Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/register/options', [StudentAuthController::class, 'registrationOptions']);

    Route::post('/register', [StudentAuthController::class, 'register'])
        ->middleware('throttle:10,1');

    // NEW: Verify email with OTP code
    Route::post('/email/verify-otp', [StudentAuthController::class, 'verifyEmailOtp'])
        ->middleware('throttle:10,1');

    // NEW: Resend OTP verification email
    Route::post('/email/resend-otp', [StudentAuthController::class, 'resendVerificationOtp'])
        ->middleware('throttle:6,1');

    /*
    |--------------------------------------------------------------------------
    | Password Reset via OTP
    |--------------------------------------------------------------------------
    */

    Route::post('/password/send-otp', [StudentAuthController::class, 'sendPasswordOtp']);
    Route::post('/password/verify-otp', [StudentAuthController::class, 'verifyPasswordOtp']);
    Route::post('/password/reset', [StudentAuthController::class, 'resetPassword']);

    /*
    |--------------------------------------------------------------------------
    | Protected Student App Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [StudentAuthController::class, 'me']);
        Route::post('/logout', [StudentAuthController::class, 'logout']);

        Route::get('/safety', [StudentSafetyController::class, 'show']);
        Route::post('/safety', [StudentSafetyController::class, 'update']);

        Route::get('/announcements', [StudentAnnouncementController::class, 'index']);

        Route::get('/notifications', [StudentNotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [StudentNotificationController::class, 'unreadCount']);
        Route::post('/notifications/read-all', [StudentNotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/{notification}/read', [StudentNotificationController::class, 'markAsRead']);

        Route::get('/departments', [StudentReportController::class, 'departments']);
        Route::get('/faculty', [StudentReportController::class, 'faculty']);

        Route::get('/reports', [StudentReportController::class, 'index']);
        Route::post('/reports', [StudentReportController::class, 'store']);
        Route::get('/reports/{report}', [StudentReportController::class, 'show']);
        Route::delete('/reports/{report}', [StudentReportController::class, 'destroy']);

        Route::post('/chatbot/message', [ChatbotController::class, 'message'])
            ->middleware('throttle:20,1');
    });
});