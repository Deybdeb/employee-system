<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Ensure user is admin before any action
     */
    private function authorizeAdmin(): void
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized access to admin panel');
        }
    }

    /**
     * Display employee directory (read-only listing)
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

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

        // Filter by job title (placeholder for future)
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
                
                // Get user record to check 2FA status
                $user = User::where('email', $employee->personal_email)->first();
                $twoFactorEnabled = false;
                if ($user && $user->twoFactorCode) {
                    $twoFactorEnabled = $user->twoFactorCode->is_enabled;
                }

                return [
                    'id' => $employee->id,
                    'name' => trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}"),
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'job_title' => 'Employee', // Placeholder
                    'department' => 'General', // Placeholder
                    'email' => $employee->work_email ?? $employee->personal_email,
                    'phone' => $employee->work_phone ?? $employee->mobile_phone,
                    'location' => $address
                        ? trim(implode(', ', array_filter([
                            $address->city,
                            $address->state_province,
                            $address->country,
                        ])))
                        : 'Not specified',
                    'initials' => strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)),
                    'two_factor_enabled' => $twoFactorEnabled,
                ];
            });

        return Inertia::render('Admin/Employees/Index', [
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
