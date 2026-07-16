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
        Schema::create('action_stats', function (Blueprint $table) {
            $table->id();

            // Actor (user performing the action)
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_type'); // App\Models\Nursing, App\Models\NursingHome, App\Models\Member

            // Action type
            $table->string('action'); // profile_view, click_call, click_contact

            // Subject (entity being acted upon)
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_type'); // App\Models\NursingProfile, App\Models\NursingHomeProfile

            // Additional data
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes for efficient querying
            $table->index(['actor_id', 'actor_type']);
            $table->index(['subject_id', 'subject_type']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_stats');
    }
};
