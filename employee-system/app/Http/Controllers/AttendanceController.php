<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\TimeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    /**
     * Display my attendance records
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee()->first();
        
        if (!$employee) {
            return back()->withErrors(['error' => 'Employee record not found.']);
        }
        
        $employeeId = $employee->id;
        $now = TimeManager::getInstance()->now();

        // Get date range from request or default to current month
        $startDate = $request->get('start_date') 
            ? \Carbon\Carbon::parse($request->get('start_date'))
            : $now->copy()->startOfMonth();
        $endDate = $request->get('end_date')
            ? \Carbon\Carbon::parse($request->get('end_date'))
            : $now->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('clock_in', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('clock_in', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'clock_in' => $attendance->clock_in->format('Y-m-d H:i:s'),
                    'clock_in_display' => $attendance->clock_in->format('M d, Y g:i A'),
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('Y-m-d H:i:s') : null,
                    'clock_out_display' => $attendance->clock_out ? $attendance->clock_out->format('M d, Y g:i A') : null,
                    'duration' => $attendance->clock_out 
                        ? $this->formatDuration($attendance->clock_in, $attendance->clock_out)
                        : 'In Progress',
                    'date' => $attendance->clock_in->format('Y-m-d'),
                ];
            });

        return Inertia::render('Time/Attendance/Index', [
            'attendances' => $attendances,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'serverTime' => $now->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Admin: View attendance records for all employees
     */
    public function admin(Request $request)
    {
        $now = TimeManager::getInstance()->now();

        // Get date range from request or default to current week
        $startDate = $request->get('start_date')
            ? \Carbon\Carbon::parse($request->get('start_date'))
            : $now->copy()->startOfWeek();
        $endDate = $request->get('end_date')
            ? \Carbon\Carbon::parse($request->get('end_date'))
            : $now->copy()->endOfWeek();

        // Get employee filter
        $employeeId = $request->get('employee_id');

        $query = Attendance::with('employee')
            ->whereBetween('clock_in', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('clock_in', 'desc');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $attendances = $query->get()->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'employee_name' => $attendance->employee 
                    ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name
                    : 'Unknown',
                'clock_in' => $attendance->clock_in->format('Y-m-d H:i:s'),
                'clock_in_display' => $attendance->clock_in->format('M d, Y g:i A'),
                'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('Y-m-d H:i:s') : null,
                'clock_out_display' => $attendance->clock_out ? $attendance->clock_out->format('M d, Y g:i A') : null,
                'duration' => $attendance->clock_out 
                    ? $this->formatDuration($attendance->clock_in, $attendance->clock_out)
                    : 'In Progress',
                'date' => $attendance->clock_in->format('Y-m-d'),
            ];
        });

        $employees = Employee::orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->first_name . ' ' . $e->last_name,
            ]);

        return Inertia::render('Time/Attendance/Admin', [
            'attendances' => $attendances,
            'employees' => $employees,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'selectedEmployee' => $employeeId,
            'serverTime' => $now->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Admin: View specific employee's punch in/out records
     */
    public function employeeRecords(Request $request, $employeeId)
    {
        $now = TimeManager::getInstance()->now();
        $employee = Employee::findOrFail($employeeId);

        // Get date range from request or default to current month
        $startDate = $request->get('start_date')
            ? \Carbon\Carbon::parse($request->get('start_date'))
            : $now->copy()->startOfMonth();
        $endDate = $request->get('end_date')
            ? \Carbon\Carbon::parse($request->get('end_date'))
            : $now->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('clock_in', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('clock_in', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'clock_in' => $attendance->clock_in->format('Y-m-d H:i:s'),
                    'clock_in_display' => $attendance->clock_in->format('M d, Y g:i A'),
                    'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('Y-m-d H:i:s') : null,
                    'clock_out_display' => $attendance->clock_out ? $attendance->clock_out->format('M d, Y g:i A') : null,
                    'duration' => $attendance->clock_out 
                        ? $this->formatDuration($attendance->clock_in, $attendance->clock_out)
                        : 'In Progress',
                    'date' => $attendance->clock_in->format('Y-m-d'),
                ];
            });

        return Inertia::render('Time/Attendance/EmployeeRecords', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
            ],
            'attendances' => $attendances,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'serverTime' => $now->format('M d, Y g:i A'),
        ]);
    }

    private function formatDuration($start, $end)
    {
        $diff = $start->diff($end);
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;
        
        return sprintf('%d hrs %d mins', $hours, $minutes);
    }
}
