<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia; //

class LeaveRequestController extends Controller
{
    public function index()
    {
        // Dummy data for testing the view
        $leaveRequests = [
            ['id' => 1, 'type' => 'Vacation', 'start_date' => '2025-12-24', 'end_date' => '2025-12-26', 'status' => 'Pending'],
            ['id' => 2, 'type' => 'Sick', 'start_date' => '2025-12-05', 'end_date' => '2025-12-05', 'status' => 'Approved'],
        ];

        return Inertia::render('Leave/index', [
            'requests' => $leaveRequests,
        ]);
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create()
    {
        $leaveTypes = ['Vacation', 'Sick', 'Personal', 'Unpaid'];

        return Inertia::render('Leave/create', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request)
    {
        // This is a placeholder; real logic would go here
        return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully.');
    }
}
