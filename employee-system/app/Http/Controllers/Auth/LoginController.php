<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'work_email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Map 'work_email' input to 'email' database column
        $loginCredentials = [
            'email' => $credentials['work_email'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($loginCredentials)) {
            $request->session()->regenerate();

            // Check if user has 2FA enabled
            $user = Auth::user();
            $twoFactor = $user->twoFactorCode;

            if ($twoFactor && $twoFactor->is_enabled) {
                // Redirect to 2FA verification page
                return redirect()->route('login.verify-2fa');
            }

            return redirect()->intended('dashboard');
        }

        return back()
            ->withErrors([
                'work_email' => 'The provided credentials do not match our records.',
            ])
            ->onlyInput('work_email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
