<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecordsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportActionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SafetyStatusController;
use App\Http\Controllers\StudentsStatusController;
use App\Http\Controllers\StaffSafetyStatusController;
use App\Http\Controllers\Admin\ChatbotResponseController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\CoCssController;

// --------------------
// AUTH (Guest)
// --------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

    // Forgot Password with OTP
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
        ->name('password.forgot');

    Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtp'])
        ->name('password.send-otp');

    Route::get('/forgot-password/verify-otp', [AuthController::class, 'showVerifyOtpForm'])
        ->name('password.verify-otp');

    Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtp'])
        ->name('password.verify-otp.submit');

    Route::get('/forgot-password/reset', [AuthController::class, 'showResetPasswordForm'])
        ->name('password.reset');

    Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])
        ->name('password.reset.submit');

    // Email Verification (guest-accessible verification link)
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');
});

// --------------------
// AUTH (Logged-in)
// --------------------
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Resend verification email (for logged-in but unverified users if needed later)
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // --------------------
    // STAFF + ADMIN
    // --------------------
    Route::middleware('role:admin,css,faculty,co_css')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.read-all');

        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        // Receive Report
        Route::get('/receive-report', [ReportController::class, 'index'])
            ->name('receive-report');

        Route::get('/receive-report/{report}', [ReportController::class, 'show'])
            ->name('receive-report.show');

        Route::post('/receive-report', [ReportController::class, 'store'])
            ->name('receive-report.store');

        Route::post('/receive-report/{report}/status', [ReportController::class, 'updateStatus'])
            ->name('receive-report.status');

        Route::post('/receive-report/{report}/recommend', [ReportActionController::class, 'recommend'])
            ->name('receive-report.recommend');

        Route::post('/receive-report/{report}/decide', [ReportActionController::class, 'decide'])
            ->name('receive-report.decide');

        Route::post('/receive-report/{report}/action-taken', [ReportActionController::class, 'actionTaken'])
            ->name('receive-report.action-taken');

        // Records
        Route::get('/records', [RecordsController::class, 'index'])
            ->name('records');

        Route::get('/records/export', [RecordsController::class, 'export'])
            ->name('records.export');

        Route::get('/records/{report}', [RecordsController::class, 'show'])
            ->name('records.show');

        // Students Status
        Route::get('/students-status', [StudentsStatusController::class, 'index'])
            ->name('students-status');

        // My Safety Status (self update)
        Route::get('/safety-status', [SafetyStatusController::class, 'index'])
            ->name('safety-status');

        Route::post('/safety-status', [SafetyStatusController::class, 'update'])
            ->name('safety-status.update');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])
            ->name('settings');

        Route::post('/settings', [SettingsController::class, 'update'])
            ->name('settings.update');

        Route::post('/settings/change-password', [SettingsController::class, 'changePassword'])
            ->name('settings.change-password');
    });

    // --------------------
    // CSS ONLY
    // --------------------
    Route::middleware('role:css')->group(function () {

        // Manage Co-CSS
        Route::get('/css/co-css', [CoCssController::class, 'index'])
            ->name('css.cocss.index');

        Route::post('/css/co-css', [CoCssController::class, 'store'])
            ->name('css.cocss.store');

        Route::put('/css/co-css/{user}', [CoCssController::class, 'update'])
            ->name('css.cocss.update');

        Route::post('/css/co-css/{user}/toggle', [CoCssController::class, 'toggle'])
            ->name('css.cocss.toggle');
    });

    // --------------------
    // ADMIN + CSS
    // --------------------
    Route::middleware('role:admin,css')->group(function () {
        // Assign Co-CSS to Report
        Route::post('/receive-report/{report}/assign-co-css', [ReportController::class, 'assignCoCss'])
            ->name('receive-report.assign-co-css');
    });

    // --------------------
    // ADMIN ONLY
    // --------------------
    Route::middleware('role:admin')->group(function () {

        // Announcements
        Route::get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::post('/announcements', [AnnouncementController::class, 'store'])
            ->name('announcements.store');

        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');

        // Manage
        Route::get('/manage', [ManageController::class, 'index'])
            ->name('manage');

        Route::post('/manage', [ManageController::class, 'store'])
            ->name('manage.store');

        Route::delete('/manage/{user}', [ManageController::class, 'destroy'])
            ->name('manage.destroy');

        // Staff Safety Status
        Route::get('/staff-safety-status', [StaffSafetyStatusController::class, 'index'])
            ->name('staff-safety-status.index');

        Route::post('/staff-safety-status/{user}', [StaffSafetyStatusController::class, 'update'])
            ->name('staff-safety-status.update');

        // Manage Staff
        Route::get('/admin/manage-staff', [AdminStaffController::class, 'index'])
            ->name('admin.staff.index');

        Route::get('/admin/manage-staff/create', [AdminStaffController::class, 'create'])
            ->name('admin.staff.create');

        Route::post('/admin/manage-staff', [AdminStaffController::class, 'store'])
            ->name('admin.staff.store');

        Route::post('/admin/manage-staff/{user}/toggle', [AdminStaffController::class, 'toggle'])
            ->name('admin.staff.toggle');

        // Chatbot Scripts
        Route::get('/chatbot-responses', [ChatbotResponseController::class, 'index'])
            ->name('admin.chatbot.index');

        Route::get('/chatbot-responses/create', [ChatbotResponseController::class, 'create'])
            ->name('admin.chatbot.create');

        Route::post('/chatbot-responses', [ChatbotResponseController::class, 'store'])
            ->name('admin.chatbot.store');

        Route::get('/chatbot-responses/{chatbotResponse}/edit', [ChatbotResponseController::class, 'edit'])
            ->name('admin.chatbot.edit');

        Route::put('/chatbot-responses/{chatbotResponse}', [ChatbotResponseController::class, 'update'])
            ->name('admin.chatbot.update');

        Route::delete('/chatbot-responses/{chatbotResponse}', [ChatbotResponseController::class, 'destroy'])
            ->name('admin.chatbot.destroy');
    });
});

// Default
Route::get('/', function () {
    return redirect()->route('login');
});