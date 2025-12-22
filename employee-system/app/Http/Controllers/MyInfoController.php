<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'addresses' => []
        ]);
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
        $name = trim($validated['first_name'] . ' ' . 
                    ($validated['middle_name'] ?? '') . ' ' . 
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
}
