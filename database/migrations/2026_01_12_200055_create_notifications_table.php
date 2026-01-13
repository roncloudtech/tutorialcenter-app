<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Who the notification is for
            $table->string('notifiable_type'); // Staff, Guardian, Student
            $table->unsignedBigInteger('notifiable_id');

            // Optional subject of notification (polymorphic)
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('type'); // action type, e.g., login, payment_made
            $table->text('message');
            $table->boolean('is_read')->default(false);

            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
