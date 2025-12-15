<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\TimeManager;

class AttendanceService
{
    private string $timezone;

    public function __construct()
    {
        $this->timezone = 'Asia/Manila';
    }

    public function getDashboardData(Collection $allAttendances): array
    {
        $now = TimeManager::getInstance()->now();
        $startOfWeek = $now->copy()->startOfWeek();

        $todayAttendances = $allAttendances->filter(function ($att) use ($now) {
            $clockIn = Carbon::parse($att->clock_in)->utc();
            $isActive = is_null($att->clock_out);
            return $clockIn->isSameDay($now) || $isActive;
        });

        $currentRecord = $allAttendances->whereNull('clock_out')->last();
        $isClockedIn = (bool) $currentRecord;

        $todayData = $this->getTodayDurationData($todayAttendances, $now);
        $lastActionFormatted = $this->getLastActionFormatted($allAttendances, $isClockedIn);

        return [
            'stats' => [
                'is_clocked_in' => $isClockedIn,
                'today_duration' => $todayData['formatted'],
                'today_seconds' => $todayData['seconds'], // Passed for progress bar
                'week_duration' => $this->getWeekDuration($allAttendances),
                'chart' => $this->getChartData($allAttendances, $now, $startOfWeek),
                'last_action_label' => $isClockedIn ? 'Timed In' : 'Timed Out',
                'last_action_sub_label' => $isClockedIn ? 'Timed In:' : 'Timed Out:',
                'last_action_time_formatted' => $lastActionFormatted,
                'week_date_range_formatted' => $startOfWeek->format('M d') . ' - ' . $startOfWeek->copy()->addDays(6)->format('M d'),
            ]
        ];
    }

    private function getTodayDurationData(Collection $todayAttendances, Carbon $now): array
    {
        $totalSeconds = $todayAttendances->reduce(function ($carry, $att) use ($now) {
            $clockIn = Carbon::parse($att->clock_in)->utc();

            $clockOut = $att->clock_out
                ? Carbon::parse($att->clock_out)->utc()
                : $now->copy()->utc();

            // Use timestamp subtraction to avoid Carbon diff issues
            $diff = $clockOut->timestamp - $clockIn->timestamp;

            return $carry + max(0, $diff);
        }, 0);

        return [
            'seconds' => $totalSeconds,
            'formatted' => floor($totalSeconds / 3600) . 'h ' . floor(($totalSeconds / 60) % 60) . 'm'
        ];
    }

    private function getLastActionFormatted(Collection $allAttendances, bool $isClockedIn): string
    {
        $lastActionTime = null;
        if ($isClockedIn) {
            $lastActionTime = $allAttendances->whereNull('clock_out')->last()->clock_in;
        } else {
            $lastRecord = $allAttendances->sortByDesc(function ($att) {
                return $att->clock_out ?? $att->clock_in;
            })->first();

            if ($lastRecord) {
                $lastActionTime = $lastRecord->clock_out ?? $lastRecord->clock_in;
            }
        }

        if (!$lastActionTime) {
            return 'Not yet today';
        }
        return Carbon::parse($lastActionTime)->tz($this->timezone)->format('M jS \a\t g:i A');
    }

    private function getWeekDuration(Collection $attendances): string
    {
        $totalSeconds = $attendances->reduce(function ($carry, $att) {
             if ($att->clock_out) {
                $clockIn = Carbon::parse($att->clock_in)->utc();
                $clockOut = Carbon::parse($att->clock_out)->utc();
                return $carry + max(0, $clockOut->timestamp - $clockIn->timestamp);
            }
            return $carry;
        }, 0);
        return floor($totalSeconds / 3600) . 'h ' . floor(($totalSeconds / 60) % 60) . 'm';
    }

    private function getChartData(Collection $attendances, Carbon $now, Carbon $startOfWeek): array
    {
        $chartData = [];
        $maxHoursForChart = 12;

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dailyTotalSeconds = $attendances->reduce(function ($carry, $att) use ($date, $now) {
                $clockIn = Carbon::parse($att->clock_in)->utc();
                if (!$clockIn->isSameDay($date)) {
                    return $carry;
                }
                $clockOut = $att->clock_out
                    ? Carbon::parse($att->clock_out)->utc()
                    : $now->copy()->utc();

                $endOfDay = $date->copy()->endOfDay()->utc();

                if ($clockOut->gt($endOfDay)) {
                    $clockOut = $endOfDay;
                }

                $diff = $clockOut->timestamp - $clockIn->timestamp;
                return $carry + max(0, $diff);
            }, 0);

            $hours = $dailyTotalSeconds / 3600;

            $chartData[] = [
                'day' => $date->format('D'),
                // Format to 2 decimal places always (e.g., "8.00")
                'hours' => number_format($hours, 2),
                'height' => $maxHoursForChart > 0 ? min(100, ($hours / $maxHoursForChart) * 100) : 0,
            ];
        }
        return $chartData;
    }
}