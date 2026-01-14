<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('name', 255);
            $table->string('relationship', 100);
            $table->string('home_phone', 20)->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
