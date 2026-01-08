<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OvertimeRequestController extends Controller
{
    /**
     * Display a listing of the authenticated user's overtime requests.
     */
    public function index()
    {
        $overtimeRequests = OvertimeRequest::where('user_id', Auth::id())
            ->with(['reviewer:id,name'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'date' => $request->date->format('Y-m-d'),
                    'start_time' => Carbon::parse($request->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($request->end_time)->format('H:i'),
                    'hours' => number_format($request->hours, 2),
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('M d, Y'),
                    'reviewer_name' => $request->reviewer?->name,
                    'reviewed_at' => $request->reviewed_at?->format('M d, Y'),
                ];
            });

        return Inertia::render('Overtime/index', [
            'requests' => $overtimeRequests,
        ]);
    }

    /**
     * Store a newly created overtime request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:1000',
        ]);

        // Calculate hours between start and end time
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);
        $hours = $endTime->diffInMinutes($startTime) / 60;

        OvertimeRequest::create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'hours' => $hours,
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request submitted successfully.');
    }

    /**
     * Display all overtime requests for HR/Admin management.
     */
    public function admin()
    {
        // Check if user is admin/HR
        if (! Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $overtimeRequests = OvertimeRequest::with(['user:id,name,email', 'reviewer:id,name'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user_name' => $request->user->name,
                    'user_email' => $request->user->email,
                    'date' => $request->date->format('Y-m-d'),
                    'start_time' => Carbon::parse($request->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($request->end_time)->format('H:i'),
                    'hours' => number_format($request->hours, 2),
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('M d, Y'),
                    'reviewer_name' => $request->reviewer?->name,
                    'reviewed_at' => $request->reviewed_at?->format('M d, Y H:i'),
                ];
            });

        return Inertia::render('Overtime/Admin', [
            'requests' => $overtimeRequests,
        ]);
    }

    /**
     * Approve an overtime request (HR/Admin only).
     */
    public function approve($id)
    {
        if (! Auth::user()->is_admin) {
            return back()->with('error', 'Unauthorized access.');
        }

        $overtimeRequest = OvertimeRequest::findOrFail($id);

        $overtimeRequest->update([
            'status' => 'Approved',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Overtime request approved successfully.');
    }

    /**
     * Decline an overtime request (HR/Admin only).
     */
    public function decline($id)
    {
        if (! Auth::user()->is_admin) {
            return back()->with('error', 'Unauthorized access.');
        }

        $overtimeRequest = OvertimeRequest::findOrFail($id);

        $overtimeRequest->update([
            'status' => 'Rejected',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Overtime request declined.');
    }

    /**
     * Cancel a pending overtime request (Employee).
     */
    public function cancel($id)
    {
        $overtimeRequest = OvertimeRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'Pending')
            ->firstOrFail();

        $overtimeRequest->update([
            'status' => 'Cancelled',
        ]);

        return back()->with('success', 'Overtime request cancelled.');
    }
}
