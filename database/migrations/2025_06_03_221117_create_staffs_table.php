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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();

            //staffs information
            $table->string('staff_id', 10)->unique(); // Unique staff ID 'TC25071234'
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('password');
            $table->enum('gender', ['Male', 'Female', 'Others'])->default('Male');
            $table->enum('staff_role', ['admin','tutor', 'adviser', 'staff'])->default('staff');
            $table->string('profile_picture')->nullable();
            $table->string('date_of_birth')->nullable();

            // // Verification fields
            $table->string('email_verified_at')->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->boolean('verified')->default(false);

            //status
            $table->enum('status', ['active', 'inactive', 'disabled'])->default('inactive');
            $table->string('home_address')->nullable();
            $table->unsignedBigInteger('indected_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
