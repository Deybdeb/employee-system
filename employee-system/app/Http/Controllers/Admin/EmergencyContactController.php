<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmergencyContactController extends Controller
{
    /**
     * Validate Philippine mobile phone number (+63 format or 09 format)
     */
    private function validatePhilippineMobile($number)
    {
        $cleaned = preg_replace('/[\s\-()]/i', '', $number);

        return preg_match('/^(\+639|09)\d{9}$/', $cleaned);
    }

    /**
     * Validate Philippine landline number
     */
    private function validatePhilippineLandline($number)
    {
        $cleaned = preg_replace('/[\s\-()]/i', '', $number);

        return preg_match('/^(\+63|0)?\d{1,2}\d{4}\d{4}$/', $cleaned);
    }

    /**
     * Display all emergency contacts for all employees
     */
    public function index()
    {
        $employees = Employee::select('id', 'first_name', 'last_name', 'personal_email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $emergencyContacts = EmergencyContact::with('employee:id,first_name,last_name')
            ->get();

        return Inertia::render('Admin/EmergencyContacts', [
            'employees' => $employees,
            'emergencyContacts' => $emergencyContacts,
        ]);
    }

    /**
     * Store a new emergency contact for any employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'home_phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'work_phone' => 'nullable|string|max:20',
        ]);

        // Ensure at least one phone number is provided
        if (empty($validated['home_phone']) && empty($validated['mobile_phone']) && empty($validated['work_phone'])) {
            return back()->withErrors(['phones' => 'At least one phone number must be provided']);
        }

        // Validate phone number formats
        if (! empty($validated['home_phone'])) {
            if (! $this->validatePhilippineLandline($validated['home_phone'])) {
                return back()->withErrors(['home_phone' => 'Invalid landline format. Use format like (02) 1234-5678 or +63 2 1234 5678']);
            }
        }

        if (! empty($validated['mobile_phone'])) {
            if (! $this->validatePhilippineMobile($validated['mobile_phone'])) {
                return back()->withErrors(['mobile_phone' => 'Invalid mobile format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX']);
            }
        }

        if (! empty($validated['work_phone'])) {
            if (! $this->validatePhilippineMobile($validated['work_phone'])) {
                return back()->withErrors(['work_phone' => 'Invalid work phone format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX']);
            }
        }

        EmergencyContact::create($validated);

        return redirect()->back()->with('success', 'Emergency contact added successfully');
    }

    /**
     * Update an emergency contact
     */
    public function update(Request $request, $id)
    {
        $emergencyContact = EmergencyContact::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'home_phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'work_phone' => 'nullable|string|max:20',
        ]);

        // Ensure at least one phone number is provided
        if (empty($validated['home_phone']) && empty($validated['mobile_phone']) && empty($validated['work_phone'])) {
            return back()->withErrors(['phones' => 'At least one phone number must be provided']);
        }

        // Validate phone number formats
        if (! empty($validated['home_phone'])) {
            if (! $this->validatePhilippineLandline($validated['home_phone'])) {
                return back()->withErrors(['home_phone' => 'Invalid landline format. Use format like (02) 1234-5678 or +63 2 1234 5678']);
            }
        }

        if (! empty($validated['mobile_phone'])) {
            if (! $this->validatePhilippineMobile($validated['mobile_phone'])) {
                return back()->withErrors(['mobile_phone' => 'Invalid mobile format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX']);
            }
        }

        if (! empty($validated['work_phone'])) {
            if (! $this->validatePhilippineMobile($validated['work_phone'])) {
                return back()->withErrors(['work_phone' => 'Invalid work phone format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX']);
            }
        }

        $emergencyContact->update($validated);

        return redirect()->back()->with('success', 'Emergency contact updated successfully');
    }

    /**
     * Delete an emergency contact
     */
    public function destroy($id)
    {
        $emergencyContact = EmergencyContact::findOrFail($id);
        $emergencyContact->delete();

        return redirect()->back()->with('success', 'Emergency contact deleted successfully');
    }
}
