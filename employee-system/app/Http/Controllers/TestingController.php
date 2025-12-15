<?php

namespace App\Http\Controllers;

use App\Services\TimeManager;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class TestingController extends Controller
{
    public function adjustTime(string $unit, int $amount)
    {
        if (!app()->isLocal()) {
            return redirect()->back();
        }

        $timeManager = TimeManager::getInstance();
        $newTime = $timeManager->adjust($unit, $amount);

        Auth::user()->update(['mocked_time' => $newTime]);

        session()->flash('success_message', 'Time traveled to: ' . $newTime->format('M d, Y g:i A'));
        return redirect()->back(303);
    }

    public function resetTime()
    {
        if (!app()->isLocal()) {
            return redirect()->back();
        }

        Auth::user()->update(['mocked_time' => null]);

        $timeManager = TimeManager::getInstance();
        $timeManager->reset();

        session()->flash('success_message', 'Time has been reset to current real time.');
        return redirect()->back(303);
    }

    public function clearAttendances()
    {
        if (app()->isLocal()) {
            Attendance::where('employee_id', Auth::id())->delete();
            session()->flash('success_message', 'All your time entries have been cleared.');
        }
        return redirect()->back(303);
    }
}
