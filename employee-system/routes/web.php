<?php

use App\Http\Controllers\Admin\EmergencyContactController as AdminEmergencyContactController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\MyInfoController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\TestingController; // Your new controller
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
        Route::get('/emergency-contacts', [MyInfoController::class, 'showEmergencyContacts'])->name('my-info.emergency-contacts');
        Route::post('/emergency-contacts', [MyInfoController::class, 'addEmergencyContact'])->name('my-info.emergency-contacts.store');
        Route::put('/emergency-contacts/{id}', [MyInfoController::class, 'updateEmergencyContact'])->name('my-info.emergency-contacts.update');
        Route::delete('/emergency-contacts/{id}', [MyInfoController::class, 'deleteEmergencyContact'])->name('my-info.emergency-contacts.destroy');
    });
    // ---------------------------

    // --- ADMIN EMERGENCY CONTACTS MODULE ROUTES ---
    Route::prefix('admin')->group(function () {
        Route::get('/emergency-contacts', [AdminEmergencyContactController::class, 'index'])->name('admin.emergency-contacts.index');
        Route::post('/emergency-contacts', [AdminEmergencyContactController::class, 'store'])->name('admin.emergency-contacts.store');
        Route::put('/emergency-contacts/{id}', [AdminEmergencyContactController::class, 'update'])->name('admin.emergency-contacts.update');
        Route::delete('/emergency-contacts/{id}', [AdminEmergencyContactController::class, 'destroy'])->name('admin.emergency-contacts.destroy');
    });
    // ---------------------------
});

if (App::isLocal()) {
    // --- TESTING ROUTES ---
    Route::get('/testing', [TestingController::class, 'index'])->name('testing.index');
}
