<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create("employees", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("first_name", 100);
            $table->string("middle_name", 100)->nullable();
            $table->string("last_name", 100);
            $table->date("date_of_birth")->nullable();
            $table->string("gender", 20)->nullable();
            $table->string("marital_status", 20)->nullable();
            $table->integer("nationality_id")->nullable();
            $table->string("other_id", 50)->nullable();
            $table->string("drivers_license_number", 50)->nullable();
            $table->date("license_expiry_date")->nullable();
            $table->string("personal_email", 255)->unique();
            $table->string("work_email", 255)->unique()->nullable();
            $table->string("mobile_phone", 20)->nullable()->unique();
            $table->string("home_phone", 20)->nullable();
            $table->string("work_phone", 20)->nullable();
            $table->string("password");
            $table->boolean("is_2fa_enabled")->default(false);
            $table->uuid("manager_id")->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists("employees");
    }
};