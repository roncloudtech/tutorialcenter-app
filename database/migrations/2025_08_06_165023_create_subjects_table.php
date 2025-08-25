<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');

            // Store courses and tutors as JSON
            $table->json('courses_ids')->nullable();
            $table->json('tutors_assignees')->nullable();
            $table->json('departments')->nullable();

            // Link to the staff who created the course
            $table->foreignId('created_by')->constrained('staffs')->onDelete('cascade');

            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
