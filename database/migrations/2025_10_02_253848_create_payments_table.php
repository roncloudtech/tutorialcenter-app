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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->enum('payment_method', ['bank transfer', 'card', 'paystack']);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded']);
            $table->decimal('amount', 10, 2);
            $table->dateTime('payment_date')->nullable();
            $table->enum('duration', ['monthly', 'quarterly', 'half_year', 'annually']);
            $table->dateTime('due_date')->nullable();
            $table->string('reference_number');
            $table->softDeletes(); // deleted_at
            $table->timestamps(); // created_at, updated_at

            // Foreign Keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
