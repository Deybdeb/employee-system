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
        // 1. ADD THE CALL TO YOUR USER SEEDER
        // This is the standard, clean way to run your specific seeder.
        $this->call(UserSeeder::class); 

        // 2. REMOVE THE FACTORY CODE YOU HAD HERE
        // (You can use the factory in UserSeeder.php if you want more users,
        // but it was causing issues here)
        
        // Example if you still want a quick factory user (but less clean):
        // User::factory()->create([
        //     "name" => "Test User",
        //     "email" => "test@example.com",
        // ]);
    }
}