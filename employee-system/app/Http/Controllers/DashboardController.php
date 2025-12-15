<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\TimeManager;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $attendanceService = new AttendanceService();
        $now = TimeManager::getInstance()->now();
        $employeeId = Auth::id();

        $activeAttendance = Attendance::where('employee_id', $employeeId)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        $startOfWeek = $now->copy()->startOfWeek()->startOfDay();
        $weeklyCompleted = Attendance::where('employee_id', $employeeId)
            ->where('clock_in', '>=', $startOfWeek)
            ->whereNotNull('clock_out')
            ->get();

        $allRelevantAttendances = $weeklyCompleted;
        if ($activeAttendance) {
            $allRelevantAttendances->push($activeAttendance);
        }

        $dashboardData = $attendanceService->getDashboardData($allRelevantAttendances);
        $serverTime = $now->format('M d, Y g:i A');

        return Inertia::render('Dashboard', [
            'stats' => $dashboardData['stats'],
            'isLocal' => app()->isLocal(),
            'flash' => ['success_message' => session('success_message')],
            'serverTime' => $serverTime,
        ]);
    }

    public function clockIn()
    {
        $now = TimeManager::getInstance()->now();
        $employeeId = Auth::id();

        $hasActiveSession = Attendance::where('employee_id', $employeeId)
            ->whereNull('clock_out')
            ->exists();

        if (!$hasActiveSession) {
            Attendance::create([
                'employee_id' => $employeeId,
                'clock_in' => $now,
            ]);
        }
        return redirect()->back();
    }

    public function clockOut()
    {
        $now = TimeManager::getInstance()->now();
        $attendance = Attendance::where('employee_id', Auth::id())
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        if ($attendance) {
            $attendance->update(['clock_out' => $now]);
        }
        return redirect()->back();
    }
}
