<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset request form.
     */
    public function requestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Generate and send OTP to user's email.
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We could not find a user with that email address.',
            ]);
        }

        // Generate a 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in remember_token with timestamp
        $user->remember_token = $otp . '|' . now()->timestamp;
        $user->save();

        // In production, you would send this OTP via email
        // Mail::to($user->email)->send(new OTPMail($otp));

        // Redirect to OTP verification page with the OTP shown (for development only)
        return redirect()->route('password.verify', ['email' => $request->email])
            ->with('otp', $otp) // In production, remove this line
            ->with('status', 'OTP has been generated! (In production, this would be sent to your email)');
    }

    /**
     * Display the OTP verification form.
     */
    public function verifyForm(Request $request)
    {
        return Inertia::render('Auth/VerifyOTP', [
            'email' => $request->email,
        ]);
    }

    /**
     * Verify the OTP and show password reset form.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->remember_token) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP. Please request a new one.',
            ]);
        }

        // Parse the stored OTP and timestamp
        $parts = explode('|', $user->remember_token);
        if (count($parts) !== 2) {
            return back()->withErrors([
                'otp' => 'Invalid OTP format. Please request a new one.',
            ]);
        }

        [$storedOTP, $timestamp] = $parts;

        // Check if OTP is expired (15 minutes)
        if (now()->timestamp - $timestamp > 900) {
            return back()->withErrors([
                'otp' => 'OTP has expired. Please request a new one.',
            ]);
        }

        // Verify OTP
        if ($request->otp !== $storedOTP) {
            return back()->withErrors([
                'otp' => 'Invalid OTP. Please try again.',
            ]);
        }

        // OTP is valid, redirect to password reset form
        return redirect()->route('password.reset', ['email' => $request->email])
            ->with('status', 'OTP verified successfully! Please enter your new password.');
    }

    /**
     * Display the password reset form.
     */
    public function resetForm(Request $request)
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'User not found.',
            ]);
        }

        // Check if user has a valid OTP (as a security check)
        if (!$user->remember_token) {
            return back()->withErrors([
                'email' => 'Invalid password reset session. Please start over.',
            ]);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->remember_token = null; // Clear the OTP
        $user->save();

        // Redirect to login with success message
        return redirect()->route('login')
            ->with('status', 'Your password has been reset successfully! You can now log in with your new password.');
    }
}
