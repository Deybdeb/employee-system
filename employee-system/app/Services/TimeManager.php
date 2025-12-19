<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TimeManager
{
    private static ?self $instance = null;

    private ?Carbon $now = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function now(): Carbon
    {
        if ($this->now === null) {
            if (app()->isLocal() && session()->has('mocked_time')) {
                $this->now = Carbon::parse(session('mocked_time'));
            } elseif (app()->isLocal() && Auth::check() && Auth::user()->mocked_time) {
                $this->now = Auth::user()->mocked_time;
            } else {
                $this->now = Carbon::now();
            }
        }

        return $this->now->copy();
    }

    public function adjust(string $unit, int $amount): Carbon
    {
        $currentTime = $this->now();
        $newTime = $currentTime->{'add'.ucfirst($unit).'s'}($amount);

        $this->now = $newTime->copy();
        session(['mocked_time' => $newTime->toDateTimeString()]);

        return $newTime;
    }

    public function reset(): void
    {
        session()->forget('mocked_time');
        $this->now = null;
    }
}
