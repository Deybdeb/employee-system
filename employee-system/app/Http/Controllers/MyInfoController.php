<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Address;

class MyInfoController extends Controller
{
    private function getEmployeeData()
    {
        $employee = Auth::user();
        $employee->refresh();
        $employee->load('addresses');
        return $employee;
    }

    public function showPersonal()
    {
        return Inertia::render('MyInfo/Edit', [
            'employee' => $this->getEmployeeData()
        ]);
    }

    public function showContact()
    {
        $employee = $this->getEmployeeData();
        return Inertia::render('MyInfo/Contact', [
            'employee' => $employee,
            'address' => $employee->addresses->where('type', 'home')->first()
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
            'other_id' => 'nullable|string|max:50',
            'drivers_license_number' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
        ]);

        $user->update($validated);

        return redirect()->back();
    }

    public function updateContact(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'street_1' => 'nullable|string|max:255',
            'street_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|integer',
            'home_phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'work_phone' => 'nullable|string|max:20',
            'work_email' => 'nullable|email|max:255',
            'personal_email' => 'required|email|max:255|unique:employees,personal_email,'.$user->id,
        ]);

        $user->update([
            'home_phone' => $validated['home_phone'] ?? null,
            'mobile_phone' => $validated['mobile_phone'] ?? null,
            'work_phone' => $validated['work_phone'] ?? null,
            'work_email' => $validated['work_email'] ?? null,
            'personal_email' => $validated['personal_email'],
        ]);

        Address::updateOrCreate(
            ['employee_id' => $user->id, 'type' => 'home'],
            [
                'street_1' => $validated['street_1'] ?? null,
                'street_2' => $validated['street_2'] ?? null,
                'city' => $validated['city'] ?? null,
                'state_province' => $validated['state_province'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'country_id' => $validated['country_id'] ?? null,
            ]
        );

        return redirect()->back();
    }
}