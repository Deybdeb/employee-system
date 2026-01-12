<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'user123@example.com')->first();
if ($user) {
    echo "User: " . $user->email . " (ID: " . $user->id . ")\n";
}

$employee = \App\Models\Employee::where('personal_email', 'user123@example.com')->first();
if ($employee) {
    echo "Employee: " . $employee->personal_email . " (ID: " . $employee->id . ")\n";
    $timesheets = \App\Models\Timesheet::where('employee_id', $employee->id)->get();
    echo "Timesheets: " . count($timesheets) . "\n";
    foreach ($timesheets as $ts) {
        echo "  - " . $ts->week_start_date . " to " . $ts->week_end_date . " (" . $ts->status . ") - Entries: " . $ts->entries->count() . "\n";
    }
}
