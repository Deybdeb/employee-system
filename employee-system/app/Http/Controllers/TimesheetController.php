<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Services\TimeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TimesheetController extends Controller
{
    /**
     * Display the user's timesheets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee()->first();

        \Log::info('Timesheet Index - User ID: '.$user->id.', Employee: '.json_encode($employee));

        if (! $employee) {
            \Log::error('Employee record not found for user: '.$user->id);

            return back()->withErrors(['error' => 'Employee record not found for user ID: '.$user->id.'. Please contact administrator.']);
        }

        $employeeId = $employee->id;
        \Log::info('Timesheet Index - Using Employee ID: '.$employeeId);
        $now = TimeManager::getInstance()->now();

        // Get the week from request or use current week
        $weekStart = $request->get('week_start')
            ? \Carbon\Carbon::parse($request->get('week_start'))->startOfWeek()
            : $now->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Find or create timesheet for this week
        $timesheet = Timesheet::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'week_start_date' => $weekStart->toDateString(),
            ],
            [
                'week_end_date' => $weekEnd->toDateString(),
                'status' => 'draft',
            ]
        );

        // Load entries
        $timesheet->load('entries');

        // Get all timesheets for this employee
        $timesheets = Timesheet::where('employee_id', $employeeId)
            ->orderBy('week_start_date', 'desc')
            ->get();

        return Inertia::render('Time/Timesheets/Index', [
            'timesheet' => $timesheet,
            'timesheets' => $timesheets,
            'currentWeekStart' => $weekStart->toDateString(),
            'currentWeekEnd' => $weekEnd->toDateString(),
            'serverTime' => $now->format('M d, Y g:i A'),
        ]);
    }

    /**
     * Store or update timesheet entries
     */
    public function store(Request $request)
    {
        $request->validate([
            'timesheet_id' => 'required|exists:timesheets,id',
            'entries' => 'required|array',
            'entries.*.project' => 'nullable|string|max:255',
            'entries.*.activity' => 'nullable|string|max:255',
            'entries.*.date' => 'required|date',
            'entries.*.hours' => 'required|numeric|min:0|max:24',
        ]);

        $timesheet = Timesheet::findOrFail($request->timesheet_id);

        $user = Auth::user();
        $employee = $user->employee()->first();

        if (! $employee || $timesheet->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        // Check if timesheet is editable
        if ($timesheet->status !== 'draft' && $timesheet->status !== 'rejected') {
            return back()->withErrors(['error' => 'This timesheet cannot be edited.']);
        }

        // Delete existing entries and recreate
        $timesheet->entries()->delete();

        foreach ($request->entries as $entry) {
            TimesheetEntry::create([
                'timesheet_id' => $timesheet->id,
                'project' => $entry['project'] ?? null,
                'activity' => $entry['activity'] ?? null,
                'date' => $entry['date'],
                'hours' => $entry['hours'],
                'notes' => $entry['notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Timesheet saved successfully.');
    }

    /**
     * Submit timesheet for approval
     */
    public function submit(Request $request, $id)
    {
        $timesheet = Timesheet::findOrFail($id);

        $user = Auth::user();
        $employee = $user->employee()->first();

        // Verify ownership
        if (! $employee || $timesheet->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        if ($timesheet->status !== 'draft' && $timesheet->status !== 'rejected') {
            return back()->withErrors(['error' => 'This timesheet has already been submitted.']);
        }

        $timesheet->update([
            'status' => 'submitted',
            'submitted_at' => TimeManager::getInstance()->now(),
        ]);

        return back()->with('success', 'Timesheet submitted for approval.');
    }

    /**
     * Admin: View all submitted timesheets
     */
    public function admin(Request $request)
    {
        $timesheets = Timesheet::with(['employee', 'entries'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $allTimesheets = Timesheet::with(['employee', 'entries'])
            ->orderBy('week_start_date', 'desc')
            ->get();

        return Inertia::render('Time/Timesheets/Admin', [
            'pendingTimesheets' => $timesheets,
            'allTimesheets' => $allTimesheets,
        ]);
    }

    /**
     * Admin: Approve a timesheet
     */
    public function approve($id)
    {
        $timesheet = Timesheet::findOrFail($id);

        $timesheet->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => TimeManager::getInstance()->now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Timesheet approved successfully.');
    }

    /**
     * Admin: Reject a timesheet
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $timesheet = Timesheet::findOrFail($id);

        $timesheet->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Timesheet rejected.');
    }
}
