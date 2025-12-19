<?php

namespace Database\Seeders; // Ensures the class is properly namespaced

use App\Models\User; // Keep this if you use the User Model in other seeders
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Set existing admin user as admin
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->update(['is_admin' => true]);
            $this->command->info('✓ admin@example.com set as admin');
        } else {
            $this->command->warn('✗ admin@example.com not found');
        }
        
        // Create regular employee user
        $regularUser = User::where('email', 'user123@example.com')->first();
        if (!$regularUser) {
            User::create([
                'name' => 'user',
                'email' => 'user123@example.com',
                'password' => bcrypt('password123'),
                'is_admin' => false,
            ]);
            $this->command->info('✓ user123@example.com created as regular employee');
        } else {
            // Update existing user
            $regularUser->update([
                'name' => 'user',
                'email' => 'user123@example.com',
            ]);
            $this->command->info('✓ user123@example.com updated');
        }
        
        // Call other seeders
        $this->call(UserSeeder::class);
    }
}