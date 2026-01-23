<?php

use App\Http\Controllers\Admin\EmergencyContactController as AdminEmergencyContactController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\TimeLogController as AdminTimeLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\MyInfoController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\TestingController; // Your new controller
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

// Default landing: redirect root to dashboard (auth will send guests to login)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    // --- AUTHENTICATION ROUTES (MUST BE NAMED FOR FRAMEWORK REDIRECTS) ---

    // 1. Show the login form
    Route::get('login', [LoginController::class, 'create'])->name('login');

    // 2. Handle the login form submission
    Route::post('login', [LoginController::class, 'store']);

    // 3. Registration routes
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    // 4. Password reset routes with OTP
    Route::get('forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendOTP'])->name('password.email');
    Route::get('verify-otp', [PasswordResetController::class, 'verifyForm'])->name('password.verify');
    Route::post('verify-otp', [PasswordResetController::class, 'verifyOTP'])->name('password.verify.submit');
    Route::get('reset-password', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// 2FA verification (after login but before dashboard) - accessible to auth users
Route::middleware('auth')->group(function () {
    Route::get('/login/verify-2fa', [TwoFactorController::class, 'show2FAVerification'])->name('login.verify-2fa');
    Route::post('/login/verify-2fa', [TwoFactorController::class, 'verify2FA'])->name('login.verify-2fa.submit');
    Route::post('/login/2fa/regenerate', [TwoFactorController::class, 'regenerate2FA'])->name('login.2fa.regenerate');
});

Route::middleware('auth')->group(function () {
    // --- AUTHENTICATION LOGOUT ---
    // This is often named 'logout' or handled internally by Laravel
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // --- DASHBOARD AND CORE ROUTES ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- DIRECTORY MODULE ROUTES ---
    Route::get('/directory', [DirectoryController::class, 'index'])->name('directory.index');

    // --- LEAVE MODULE ROUTES ---
    Route::prefix('leave-requests')->group(function () {
        // Employee routes
        Route::get('/', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
        Route::post('/{id}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');

        // HR/Admin routes
        Route::get('/admin', [LeaveRequestController::class, 'admin'])->name('leave-requests.admin');
        Route::post('/{id}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
        Route::post('/{id}/decline', [LeaveRequestController::class, 'decline'])->name('leave-requests.decline');
        Route::delete('/{id}', [LeaveRequestController::class, 'destroy'])->name('leave-requests.destroy');
    });
    // ---------------------------

    // --- OVERTIME MODULE ROUTES ---
    Route::prefix('overtime-requests')->group(function () {
        // Employee routes
        Route::get('/', [OvertimeRequestController::class, 'index'])->name('overtime-requests.index');
        Route::post('/', [OvertimeRequestController::class, 'store'])->name('overtime-requests.store');
        Route::post('/{id}/cancel', [OvertimeRequestController::class, 'cancel'])->name('overtime-requests.cancel');

        // HR/Admin routes
        Route::get('/admin', [OvertimeRequestController::class, 'admin'])->name('overtime-requests.admin');
        Route::post('/{id}/approve', [OvertimeRequestController::class, 'approve'])->name('overtime-requests.approve');
        Route::post('/{id}/decline', [OvertimeRequestController::class, 'decline'])->name('overtime-requests.decline');
    });
    // ---------------------------

    // --- TIMESHEET MODULE ROUTES ---
    Route::prefix('timesheets')->group(function () {
        // Employee routes
        Route::get('/', [TimesheetController::class, 'index'])->name('timesheets.index');
        Route::post('/', [TimesheetController::class, 'store'])->name('timesheets.store');
        Route::post('/{id}/submit', [TimesheetController::class, 'submit'])->name('timesheets.submit');

        // HR/Admin routes
        Route::get('/admin', [TimesheetController::class, 'admin'])->name('timesheets.admin');
        Route::post('/{id}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');
        Route::post('/{id}/reject', [TimesheetController::class, 'reject'])->name('timesheets.reject');
    });
    // ---------------------------

    // --- ATTENDANCE MODULE ROUTES ---
    Route::prefix('attendance')->group(function () {
        // Employee routes
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');

        // HR/Admin routes
        Route::get('/admin', [AttendanceController::class, 'admin'])->name('attendance.admin');
        Route::post('/create-manual', [AttendanceController::class, 'createManualEntry'])->name('attendance.create-manual');
        Route::put('/{id}', [AttendanceController::class, 'updateAttendance'])->name('attendance.update');
        Route::delete('/{id}', [AttendanceController::class, 'deleteTimeLog'])->name('attendance.destroy');
        Route::get('/employee/{employeeId}', [AttendanceController::class, 'employeeRecords'])->name('attendance.employee');
    });
    // ---------------------------

    // --- MY INFO MODULE ROUTES ---
    Route::prefix('my-info')->group(function () {
        Route::get('/', [MyInfoController::class, 'index'])->name('my-info.index');
        Route::get('/personal', [MyInfoController::class, 'showPersonal'])->name('my-info.personal');
        Route::post('/personal', [MyInfoController::class, 'updatePersonal'])->name('my-info.personal.update');
        Route::get('/contact', [MyInfoController::class, 'showContact'])->name('my-info.contact');
        Route::post('/contact', [MyInfoController::class, 'updateContact'])->name('my-info.contact.update');
        Route::get('/password', [MyInfoController::class, 'showPassword'])->name('my-info.password');
        Route::post('/password', [MyInfoController::class, 'updatePassword'])->name('my-info.password.update');
        Route::get('/2fa', [MyInfoController::class, 'show2FA'])->name('my-info.2fa');
        Route::post('/2fa/setup', [MyInfoController::class, 'setup2FA'])->name('my-info.2fa.setup');
        Route::post('/2fa/enable', [MyInfoController::class, 'enable2FA'])->name('my-info.2fa.enable');
        Route::post('/2fa/disable', [MyInfoController::class, 'disable2FA'])->name('my-info.2fa.disable');
        Route::post('/2fa/regenerate', [MyInfoController::class, 'regenerate2FA'])->name('my-info.2fa.regenerate');
        Route::get('/emergency-contacts', [MyInfoController::class, 'showEmergencyContacts'])->name('my-info.emergency-contacts');
        Route::post('/emergency-contacts', [MyInfoController::class, 'addEmergencyContact'])->name('my-info.emergency-contacts.store');
        Route::put('/emergency-contacts/{id}', [MyInfoController::class, 'updateEmergencyContact'])->name('my-info.emergency-contacts.update');
        Route::delete('/emergency-contacts/{id}', [MyInfoController::class, 'deleteEmergencyContact'])->name('my-info.emergency-contacts.destroy');
    });
    // ---------------------------

    // --- TIME LOGS MODULE ROUTES ---
    Route::prefix('time-logs')->group(function () {
        // Employee routes
        Route::post('/', [TimeLogController::class, 'store'])->name('time-logs.store');
        Route::get('/my-logs', [TimeLogController::class, 'myLogs'])->name('time-logs.myLogs');
        Route::get('/latest', [TimeLogController::class, 'getLatest'])->name('time-logs.latest');
        Route::get('/{id}/photo', [TimeLogController::class, 'getPhoto'])->name('time-logs.photo');
    });
    // ---------------------------

    // --- ADMIN EMERGENCY CONTACTS MODULE ROUTES ---
    Route::prefix('admin')->group(function () {
        Route::get('/emergency-contacts', [AdminEmergencyContactController::class, 'index'])->name('admin.emergency-contacts.index');
        Route::post('/emergency-contacts', [AdminEmergencyContactController::class, 'store'])->name('admin.emergency-contacts.store');
        Route::put('/emergency-contacts/{id}', [AdminEmergencyContactController::class, 'update'])->name('admin.emergency-contacts.update');
        Route::delete('/emergency-contacts/{id}', [AdminEmergencyContactController::class, 'destroy'])->name('admin.emergency-contacts.destroy');

        // Time logs management routes (admin only)
        Route::prefix('time-logs')->group(function () {
            // Export route MUST come before {id} to avoid being treated as an ID
            Route::get('/export/csv', [AdminTimeLogController::class, 'exportCsv'])->name('admin.time-logs.export-csv');
            Route::get('/{userId}/stats', [AdminTimeLogController::class, 'getUserStats'])->name('admin.time-logs.stats');

            Route::get('/', [AdminTimeLogController::class, 'index'])->name('admin.time-logs.index');
            Route::post('/', [AdminTimeLogController::class, 'store'])->name('admin.time-logs.store');
            Route::get('/{id}', [AdminTimeLogController::class, 'show'])->name('admin.time-logs.show');
            Route::get('/{id}/photo', [AdminTimeLogController::class, 'getPhoto'])->name('admin.time-logs.photo');
            Route::put('/{id}', [AdminTimeLogController::class, 'update'])->name('admin.time-logs.update');
            Route::delete('/{id}', [AdminTimeLogController::class, 'destroy'])->name('admin.time-logs.destroy');
        });

        // Employee management routes (admin only)
        Route::prefix('employees')->group(function () {
            Route::get('/', [AdminEmployeeController::class, 'index'])->name('admin.employees.index');
            Route::post('/', [AdminEmployeeController::class, 'store'])->name('admin.employees.store');
            Route::get('/{id}', [AdminEmployeeController::class, 'show'])->name('admin.employees.show');
            Route::put('/{id}/personal', [AdminEmployeeController::class, 'updatePersonal'])->name('admin.employees.update-personal');
            Route::put('/{id}/contact', [AdminEmployeeController::class, 'updateContact'])->name('admin.employees.update-contact');
            Route::post('/{id}/disable-2fa', [AdminEmployeeController::class, 'disable2FA'])->name('admin.employees.disable-2fa');
            Route::get('/{id}/2fa-status', [AdminEmployeeController::class, 'get2FAStatus'])->name('admin.employees.2fa-status');
            Route::delete('/{id}', [AdminEmployeeController::class, 'destroy'])->name('admin.employees.destroy');
        });
    });
    // ---------------------------
});

if (App::isLocal()) {
    // --- TESTING ROUTES ---
    Route::get('/testing', [TestingController::class, 'index'])->name('testing.index');

    // Debug TOTP verification
    Route::get('/debug/totp', function () {
        $user = auth()->user();
        if (! $user || ! $user->twoFactorCode) {
            return response()->json(['error' => 'User not authenticated or 2FA not set up'], 404);
        }

        $twoFactor = $user->twoFactorCode;
        $totp = $twoFactor->getTotp();
        $currentCode = $totp->now();
        $secret = $twoFactor->secret;

        return response()->json([
            'user' => $user->email,
            'secret' => $secret,
            'current_code' => $currentCode,
            'timestamp' => now()->timestamp,
            'verification_test' => [
                'current_code_valid' => $twoFactor->verifyCode($currentCode),
                'wrong_code_valid' => $twoFactor->verifyCode('000000'),
            ],
        ]);
    })->middleware('auth');
}
