<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()
            ->select([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'work_email',
                'personal_email',
                'mobile_phone',
                'work_phone',
            ])
            ->with('addresses:id,employee_id,city,state_province,country');

        // Search by employee name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        // Filter by job title (when field is added to employees table)
        // This is a placeholder for future implementation
        if ($request->filled('job_title')) {
            // $query->where('job_title', $request->job_title);
        }

        // Filter by location
        if ($request->filled('location')) {
            $location = $request->location;
            $query->whereHas('addresses', function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                    ->orWhere('state_province', 'like', "%{$location}%")
                    ->orWhere('country', 'like', "%{$location}%");
            });
        }

        $employees = $query->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($employee) {
                $address = $employee->addresses->first();

                return [
                    'id' => $employee->id,
                    'name' => trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}"),
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'job_title' => 'Employee', // Placeholder - update when job_title field is added
                    'department' => 'General', // Placeholder - update when department field is added
                    'email' => $employee->work_email ?? $employee->personal_email,
                    'phone' => $employee->work_phone ?? $employee->mobile_phone,
                    'location' => $address
                        ? trim(implode(', ', array_filter([
                            $address->city,
                            $address->state_province,
                            $address->country,
                        ])))
                        : 'Not specified',
                    'initials' => strtoupper(substr($employee->first_name, 0, 1).substr($employee->last_name, 0, 1)),
                ];
            });

        return Inertia::render('Directory', [
            'employees' => $employees,
            'filters' => [
                'search' => $request->search,
                'job_title' => $request->job_title,
                'location' => $request->location,
            ],
            'total' => $employees->count(),
        ]);
    }
}
