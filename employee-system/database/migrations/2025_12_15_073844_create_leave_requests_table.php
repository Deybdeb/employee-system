<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            
            // 1. Employee who requested leave (Foreign Key to 'users' table)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // 2. Leave details
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');

            // 3. Leave type (e.g., Vacation, Sick, Personal)
            $table->string('type', 50);

            // 4. Status (e.g., Pending, Approved, Rejected)
            $table->string('status', 20)->default('Pending');
            
            // 5. Audit fields
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};