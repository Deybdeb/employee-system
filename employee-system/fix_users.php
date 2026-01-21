    <?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Update admin user
$admin = User::where('email', 'admin@example.com')->first();
if ($admin) {
    $admin->password = Hash::make('Password123');
    $admin->is_admin = true;
    $admin->save();
    echo "Admin user updated successfully!\n";
    echo "Email: admin@example.com\n";
    echo "Password: Password123\n";
    echo "is_admin: true\n\n";
} else {
    // Create admin if doesn't exist
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('Password123'),
        'is_admin' => true,
    ]);
    echo "Admin user created!\n";
    echo "Email: admin@example.com\n";
    echo "Password: Password123\n\n";
}

// Create or update user123
$employee = User::where('email', 'user123@example.com')->first();
if ($employee) {
    $employee->password = Hash::make('Password123');
    $employee->is_admin = false;
    $employee->save();
    echo "Employee user updated successfully!\n";
    echo "Email: user123@example.com\n";
    echo "Password: Password123\n";
} else {
    $employee = User::create([
        'name' => 'Employee User',
        'email' => 'user123@example.com',
        'password' => Hash::make('Password123'),
        'is_admin' => false,
    ]);
    echo "Employee user created!\n";
    echo "Email: user123@example.com\n";
    echo "Password: Password123\n";
}

echo "\n--- All Users ---\n";
$users = User::all(['id', 'name', 'email', 'is_admin']);
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, is_admin: ".($user->is_admin ? 'Yes' : 'No')."\n";
}
