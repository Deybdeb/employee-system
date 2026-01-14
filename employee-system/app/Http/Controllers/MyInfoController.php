<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\EmergencyContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class MyInfoController extends Controller
{
    private function getEmployeeData()
    {
        $user = Auth::user();
        $user->refresh();

        // Get employee record based on email
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        if ($employee) {
            // Load addresses relationship
            $employee->load('addresses');

            return $employee->toArray();
        }

        // If no employee record exists, return user data with empty employee fields
        return array_merge($user->toArray(), [
            'first_name' => explode(' ', $user->name)[0] ?? '',
            'middle_name' => '',
            'last_name' => count(explode(' ', $user->name)) > 1 ? explode(' ', $user->name)[1] : '',
            'personal_email' => $user->email,
            'work_email' => $user->email,
            'addresses' => [],
        ]);
    }

    /**
     * Validate Philippine mobile phone number (+63 format or 09 format)
     */
    private function validatePhilippineMobile($number)
    {
        // Remove spaces, dashes, parentheses
        $cleaned = preg_replace('/[\s\-()]/i', '', $number);

        // Accept formats: +639xxxxxxxxxx or 09xxxxxxxxxx
        return preg_match('/^(\+639|09)\d{9}$/', $cleaned);
    }

    /**
     * Validate Philippine landline number
     */
    private function validatePhilippineLandline($number)
    {
        // Remove spaces, dashes, parentheses for checking
        $cleaned = preg_replace('/[\s\-()]/i', '', $number);

        // Accept various landline formats with area codes
        return preg_match('/^(\+63|0)?\d{1,2}\d{4}\d{4}$/', $cleaned);
    }

    /**
     * Display the My Info page (redirects to personal details)
     */
    public function index()
    {
        return redirect()->route('my-info.personal');
    }

    public function showPersonal()
    {
        return Inertia::render('MyInfo/Edit', [
            'employee' => $this->getEmployeeData(),
        ]);
    }

    public function showContact()
    {
        $employee = $this->getEmployeeData();

        return Inertia::render('MyInfo/Contact', [
            'employee' => $employee,
        ]);
    }

    public function showPassword()
    {
        return Inertia::render('MyInfo/Password', [
            'employee' => $this->getEmployeeData(),
        ]);
    }

    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female',
            'marital_status' => 'nullable|string',
            'nationality_id' => 'nullable|integer',
            'other_id' => 'nullable|string|max:50',
            'drivers_license_number' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
        ]);

        // Update User name
        $name = trim($validated['first_name'].' '.
                    ($validated['middle_name'] ?? '').' '.
                    $validated['last_name']);

        $user->update(['name' => $name]);

        // Update or create Employee record
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        if ($employee) {
            $employee->update($validated);
        } else {
            \App\Models\Employee::create(array_merge($validated, [
                'personal_email' => $user->email,
                'work_email' => $user->email,
                'password' => $user->password,
            ]));
        }

        return redirect()->back()->with('success', 'Personal information updated successfully');
    }

    public function updateContact(Request $request)
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        $validated = $request->validate([
            'personal_email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'work_email' => 'nullable|email|max:255',
            'mobile_phone' => 'nullable|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'work_phone' => 'nullable|string|max:20',
            // Address fields
            'street_1' => 'nullable|string|max:255',
            'street_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'zip_postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        // Update User email
        $user->update([
            'email' => $validated['personal_email'],
        ]);

        // Update Employee record
        if ($employee) {
            $employee->update([
                'personal_email' => $validated['personal_email'],
                'work_email' => $validated['work_email'],
                'mobile_phone' => $validated['mobile_phone'],
                'home_phone' => $validated['home_phone'],
                'work_phone' => $validated['work_phone'],
            ]);

            // Update or create Address record
            $address = $employee->addresses()->first();

            if ($address) {
                $address->update([
                    'street_1' => $validated['street_1'],
                    'street_2' => $validated['street_2'],
                    'city' => $validated['city'],
                    'state_province' => $validated['state_province'],
                    'postal_code' => $validated['zip_postal_code'],
                    'country' => $validated['country'],
                ]);
            } else {
                Address::create([
                    'employee_id' => $employee->id,
                    'type' => 'home',
                    'street_1' => $validated['street_1'],
                    'street_2' => $validated['street_2'],
                    'city' => $validated['city'],
                    'state_province' => $validated['state_province'],
                    'postal_code' => $validated['zip_postal_code'],
                    'country' => $validated['country'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Contact information updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Verify current password
        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function showEmergencyContacts()
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        $emergencyContacts = [];
        if ($employee) {
            $employee->load('emergencyContacts');
            $emergencyContacts = $employee->emergencyContacts;
        }

        return Inertia::render('MyInfo/EmergencyContacts', [
            'employee' => $this->getEmployeeData(),
            'emergencyContacts' => $emergencyContacts,
        ]);
    }

    public function addEmergencyContact(Request $request)
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        if (! $employee) {
            return back()->withErrors(['error' => 'Employee record not found']);
        }

        $validated = $request->validate([
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

        EmergencyContact::create(array_merge($validated, [
            'employee_id' => $employee->id,
        ]));

        return redirect()->back()->with('success', 'Emergency contact added successfully');
    }

    public function updateEmergencyContact(Request $request, $id)
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        if (! $employee) {
            return back()->withErrors(['error' => 'Employee record not found']);
        }

        $emergencyContact = EmergencyContact::find($id);

        if (! $emergencyContact || $emergencyContact->employee_id !== $employee->id) {
            return back()->withErrors(['error' => 'Emergency contact not found']);
        }

        $validated = $request->validate([
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

    public function deleteEmergencyContact($id)
    {
        $user = Auth::user();
        $employee = \App\Models\Employee::where('personal_email', $user->email)->first();

        if (! $employee) {
            return back()->withErrors(['error' => 'Employee record not found']);
        }

        $emergencyContact = EmergencyContact::find($id);

        if (! $emergencyContact || $emergencyContact->employee_id !== $employee->id) {
            return back()->withErrors(['error' => 'Emergency contact not found']);
        }

        $emergencyContact->delete();

        return redirect()->back()->with('success', 'Emergency contact deleted successfully');
    }
}
