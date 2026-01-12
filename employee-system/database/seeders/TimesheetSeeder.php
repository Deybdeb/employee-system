<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user if it doesn't exist
        $user = User::where('email', 'user123@example.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'User 123',
                'email' => 'user123@example.com',
                'password' => Hash::make('password'),
            ]);
        }

        // Create matching employee if it doesn't exist
        $employee = Employee::where('personal_email', 'user123@example.com')->first();
        if (!$employee) {
            $employee = Employee::create([
                'first_name' => 'User',
                'last_name' => '123',
                'personal_email' => 'user123@example.com',
                'work_email' => 'user123@work.com',
                'password' => Hash::make('password'),
            ]);
        }

        // Create sample timesheets for the past 3 weeks
        $startDate = Carbon::now()->startOfWeek();
        
        for ($i = 0; $i < 3; $i++) {
            $weekStart = $startDate->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->addDays(6);

            // Check if timesheet already exists
            $timesheet = Timesheet::where('employee_id', $employee->id)
                ->where('week_start_date', $weekStart->toDateString())
                ->first();

            if (!$timesheet) {
                $timesheet = Timesheet::create([
                    'employee_id' => $employee->id,
                    'week_start_date' => $weekStart->toDateString(),
                    'week_end_date' => $weekEnd->toDateString(),
                    'status' => $i === 0 ? 'draft' : ($i === 1 ? 'submitted' : 'approved'),
                    'submitted_at' => $i > 0 ? $weekStart->copy()->addDays(5) : null,
                    'approved_at' => $i > 1 ? $weekStart->copy()->addDays(6) : null,
                    'approved_by' => $i > 1 ? 1 : null, // Assuming admin user ID is 1
                ]);

                // Create sample entries for each day of the week
                for ($day = 0; $day < 5; $day++) { // Mon-Fri
                    $entryDate = $weekStart->copy()->addDays($day);

                    TimesheetEntry::create([
                        'timesheet_id' => $timesheet->id,
                        'project' => 'Project ' . chr(65 + ($day % 3)), // Projects A, B, C rotating
                        'activity' => ['Development', 'Testing', 'Documentation', 'Meetings', 'Review'][$day],
                        'date' => $entryDate->toDateString(),
                        'hours' => 8 + rand(-1, 1), // 7-9 hours per day
                        'notes' => 'Sample entry for ' . $entryDate->format('l'),
                    ]);
                }
            }
        }

        $this->command->info('Timesheet sample data created successfully!');
    }
}
