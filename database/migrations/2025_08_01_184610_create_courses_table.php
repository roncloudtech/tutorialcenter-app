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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // WAEC, JAMB, etc.
            $table->string('slug')->unique(); // waec, jamb
            $table->text('description')->nullable();
            $table->string('course_image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->decimal('price', 10, 2)->default(0.00); // optional
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
