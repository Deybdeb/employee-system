<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the authenticated user's leave requests.
     */
    public function index()
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->with(['reviewer:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'type' => $request->type,
                    'start_date' => $request->start_date->format('Y-m-d'),
                    'end_date' => $request->end_date->format('Y-m-d'),
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('M d, Y'),
                    'reviewer_name' => $request->reviewer?->name,
                    'reviewed_at' => $request->reviewed_at?->format('M d, Y'),
                ];
            });

        return Inertia::render('Leave/index', [
            'requests' => $leaveRequests,
            'leaveTypes' => ['Vacation', 'Sick', 'Personal', 'Unpaid', 'Emergency'],
        ]);
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create()
    {
        $leaveTypes = ['Vacation', 'Sick', 'Personal', 'Unpaid', 'Emergency'];

        return Inertia::render('Leave/create', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Vacation,Sick,Personal,Unpaid,Emergency',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display all leave requests for HR/Admin management.
     */
    public function admin()
    {
        // Check if user is admin/HR
        if (! Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $leaveRequests = LeaveRequest::with(['user:id,name,email', 'reviewer:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user_name' => $request->user->name,
                    'user_email' => $request->user->email,
                    'type' => $request->type,
                    'start_date' => $request->start_date->format('Y-m-d'),
                    'end_date' => $request->end_date->format('Y-m-d'),
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('M d, Y'),
                    'reviewer_name' => $request->reviewer?->name,
                    'reviewed_at' => $request->reviewed_at?->format('M d, Y H:i'),
                ];
            });

        return Inertia::render('Leave/Admin', [
            'requests' => $leaveRequests,
        ]);
    }

    /**
     * Approve a leave request (HR/Admin only).
     */
    public function approve($id)
    {
        if (! Auth::user()->is_admin) {
            return back()->with('error', 'Unauthorized access.');
        }

        $leaveRequest = LeaveRequest::findOrFail($id);

        $leaveRequest->update([
            'status' => 'Approved',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved successfully.');
    }

    /**
     * Decline a leave request (HR/Admin only).
     */
    public function decline($id)
    {
        if (! Auth::user()->is_admin) {
            return back()->with('error', 'Unauthorized access.');
        }

        $leaveRequest = LeaveRequest::findOrFail($id);

        $leaveRequest->update([
            'status' => 'Rejected',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request declined.');
    }

    /**
     * Cancel a pending leave request (Employee).
     */
    public function cancel($id)
    {
        $leaveRequest = LeaveRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'Pending')
            ->firstOrFail();

        $leaveRequest->update([
            'status' => 'Cancelled',
        ]);

        return back()->with('success', 'Leave request cancelled.');
    }

    /**
     * Delete a leave request (HR/Admin only).
     */
    public function destroy($id)
    {
        if (! Auth::user()->is_admin) {
            return back()->with('error', 'Unauthorized access.');
        }

        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->delete();

        return back()->with('success', 'Leave request deleted successfully.');
    }
}
