<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email|unique:employees,personal_email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use a database transaction to ensure both records are created together
        DB::transaction(function () use ($request) {
            // Create the User record
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => false, // Default to non-admin
            ]);

            // Split the name for the employee record
            $nameParts = explode(' ', $request->name, 3);
            $firstName = $nameParts[0] ?? '';
            $middleName = count($nameParts) === 3 ? $nameParts[1] : null;
            $lastName = count($nameParts) >= 2 ? end($nameParts) : '';

            // Create the Employee record automatically
            Employee::create([
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'personal_email' => $request->email,
                'work_email' => $request->email, // Initially set work email same as personal
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));

            Auth::login($user);
        });

        return redirect()->route('dashboard');
    }
}
