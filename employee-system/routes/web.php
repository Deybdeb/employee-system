<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\MyInfoController;
use App\Http\Controllers\TestingController; // Your new controller
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

    // 3. Add any other guest routes here (like registration, if applicable)
});

Route::middleware('auth')->group(function () {
    // --- AUTHENTICATION LOGOUT ---
    // This is often named 'logout' or handled internally by Laravel
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // --- DASHBOARD AND CORE ROUTES ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // --- MY INFO MODULE ROUTES ---
    Route::prefix('my-info')->group(function () {
        Route::get('/', [MyInfoController::class, 'index'])->name('my-info.index');
        // Add more MyInfo routes here (e.g., PUT or POST for updates)
    });
    // ---------------------------
});

if (App::isLocal()) {
    // --- TESTING ROUTES ---
    Route::get('/testing', [TestingController::class, 'index'])->name('testing.index');
}
