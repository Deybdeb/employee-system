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
        return Inertia::render("Auth/Login");
    }
    public function store(Request $request)
{
    $credentials = $request->validate([
        "work_email" => ["required", "email"],
        "password" => ["required"],
    ]);

    // ** 1. CORRECT THE CREDENTIALS ARRAY KEY **
    $loginCredentials = [
        'email' => $credentials['work_email'], // Database column 'email' maps to input 'work_email'
        'password' => $credentials['password'], // Password key remains 'password'
    ];

    // ** 2. ATTEMPT LOGIN WITH THE CORRECTED ARRAY **
    if (Auth::attempt($loginCredentials)) {
        $request->session()->regenerate();
        return redirect()->intended("dashboard");
    }

    // ... (rest of the failure logic)
    return back()
        ->withErrors([
            "work_email" =>
                "The provided credentials do not match our records.",
        ])
        ->onlyInput("work_email");
}
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect("/login");
    }
}
