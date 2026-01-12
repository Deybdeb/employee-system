<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Optional check to prevent creating the user multiple times
        if (DB::table('users')->where('email', 'admin@example.com')->doesntExist()) {
            DB::table('users')->insert([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create corresponding Employee record
            Employee::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'personal_email' => 'admin@example.com',
                'work_email' => 'admin@example.com',
            ]);
        }
    }
}
