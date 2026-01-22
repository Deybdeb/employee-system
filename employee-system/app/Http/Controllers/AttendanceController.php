<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\TimeLog;
use App\Models\User;
use App\Services\TimeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    /**
     * Display my attendance records (Time In/Out history)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get date range from request or default to current month
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfMonth();

        // Fetch time logs instead of attendance records
        $query = TimeLog::forUser($user->id)
            ->whereBetween('timestamp', [$startDate, $endDate]);

        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $timeLogs = $query
            ->latest('timestamp')
            ->paginate(50)
            ->appends($request->query());

        return Inertia::render('Time/Attendance/Index', [
            'timeLogs' => $timeLogs,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'type' => $request->type,
            ],
            'serverTime' => now()->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Admin: View attendance records for all employees (Time In/Out)
     */
    public function admin(Request $request)
    {
        // Default date range: current week
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfWeek();
        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfWeek();

        // Get employee filter
        $employeeId = $request->employee_id;
        $typeFilter = $request->type;

        $query = TimeLog::query()
            ->whereNull('deleted_at')
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->with('user');

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        if ($typeFilter) {
            $query->where('type', $typeFilter);
        }

        $timeLogs = $query
            ->latest('timestamp')
            ->paginate(50)
            ->appends($request->query());

        $employees = User::where('is_admin', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Time/Attendance/Admin', [
            'timeLogs' => $timeLogs,
            'employees' => $employees,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'employee_id' => $employeeId,
                'type' => $typeFilter,
            ],
            'serverTime' => now()->format('M d, Y g:i A'),
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
                'name' => $employee->first_name.' '.$employee->last_name,
            ],
            'attendances' => $attendances,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'serverTime' => $now->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Admin: Create a manual time entry
     */
    public function createManualEntry(Request $request)
    {
        // Admin authorization
        abort_if(!auth()->user()->is_admin, 403);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:clock_in,clock_out',
            'timestamp' => 'required|date_format:Y-m-d\TH:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $timestamp = \Carbon\Carbon::parse($validated['timestamp']);
        $elapsedSeconds = 0;

        // If creating a clock_out, calculate elapsed time from last clock_in
        if ($validated['type'] === 'clock_out') {
            $lastClockIn = TimeLog::where('user_id', $validated['user_id'])
                ->where('type', 'clock_in')
                ->latest('timestamp')
                ->first();

            if ($lastClockIn) {
                $elapsedSeconds = $timestamp->diffInSeconds($lastClockIn->timestamp);
            }
        }

        TimeLog::create([
            'user_id' => $validated['user_id'],
            'type' => $validated['type'],
            'timestamp' => $timestamp,
            'elapsed_seconds' => $elapsedSeconds,
            'notes' => $validated['notes'] ?? null,
            'is_manual' => true,
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('attendance.admin')
            ->with('success', 'Manual time entry created successfully.');
    }

    /**
     * Admin: Update an existing time entry
     */
    public function updateAttendance(Request $request, $id)
    {
        // Admin authorization
        abort_if(!auth()->user()->is_admin, 403);

        $timeLog = TimeLog::findOrFail($id);

        $validated = $request->validate([
            'timestamp' => 'required|date_format:Y-m-d\TH:i',
            'notes' => 'nullable|string|max:500',
        ]);

        $newTimestamp = \Carbon\Carbon::parse($validated['timestamp']);

        // Update the time log
        $timeLog->update([
            'timestamp' => $newTimestamp,
            'notes' => $validated['notes'] ?? null,
        ]);

        // If this is a clock_out, recalculate elapsed seconds
        if ($timeLog->type === 'clock_out') {
            $clockIn = TimeLog::where('user_id', $timeLog->user_id)
                ->where('type', 'clock_in')
                ->where('id', '!=', $timeLog->id)
                ->latest('timestamp')
                ->first();

            if ($clockIn) {
                $elapsedSeconds = $newTimestamp->diffInSeconds($clockIn->timestamp);
                $timeLog->update(['elapsed_seconds' => $elapsedSeconds]);
            }
        }

        return redirect()->route('attendance.admin')
            ->with('success', 'Time entry updated successfully.');
    }

    /**
     * Admin: Delete a time entry (permanent deletion)
     */
    public function deleteTimeLog($id)
    {
        // Admin authorization
        abort_if(!auth()->user()->is_admin, 403);

        try {
            $timeLog = TimeLog::withTrashed()->findOrFail($id);
            $timeLog->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Time entry deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete time entry: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function formatDuration($start, $end)
    {
        $diff = $start->diff($end);
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;

        return sprintf('%d hrs %d mins', $hours, $minutes);
    }
}
