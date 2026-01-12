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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            /**
             * POLYMORPHIC ACTOR
             * Who performed the action?
             */
            $table->morphs('actor');
            // Creates:
            // actor_id (BIGINT)
            // actor_type (VARCHAR)
            // Example actor_type value:
            // App\Models\Student

            /**
             * ACTION INFORMATION
             */
            $table->string('action');
            // Examples:
            // login, logout, update_profile, submit_assignment

            /**
             * POLYMORPHIC SUBJECT
             * What was affected?
             */
            $table->nullableMorphs('subject');
            // Creates:
            // subject_id
            // subject_type
            // Can be NULL if action has no target

            /**
             * METADATA
             */
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('changes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            /**
             * PERFORMANCE INDEX
             */
            $table->index(['actor_id', 'actor_type']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
