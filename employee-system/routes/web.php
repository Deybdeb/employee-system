<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MyInfoController;
use App\Http\Controllers\TestingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;


Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () { return redirect('/dashboard'); });
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/attendance/clock-in', [DashboardController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [DashboardController::class, 'clockOut']);

    Route::get('/my-info', fn() => redirect()->route('my-info.personal'));
    Route::get('/my-info/personal', [MyInfoController::class, 'showPersonal'])->name('my-info.personal');
    Route::post('/my-info/personal', [MyInfoController::class, 'updatePersonal']);
    Route::get('/my-info/contact', [MyInfoController::class, 'showContact'])->name('my-info.contact');
    Route::post('/my-info/contact', [MyInfoController::class, 'updateContact']);

});



if (App::isLocal()) {
    Route::post('/testing/time-travel/{unit}/{amount}', [TestingController::class, 'adjustTime'])->name('testing.time-adjust');
    Route::post('/testing/time-reset', [TestingController::class, 'resetTime'])->name('testing.time-reset');

    Route::post('/testing/clear-attendances', [TestingController::class, 'clearAttendances'])->name('testing.clear-attendances');
}
