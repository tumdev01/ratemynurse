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
        Schema::create('action_stat_summaries', function (Blueprint $table) {
            $table->id();

            // Action type
            $table->string('action');

            // Subject (entity being tracked)
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_type');

            // Date for aggregation
            $table->date('date');

            // Aggregated count
            $table->unsignedInteger('count')->default(0);

            $table->timestamps();

            // Unique constraint to prevent duplicate summaries
            $table->unique(['action', 'subject_id', 'subject_type', 'date'], 'action_stat_summaries_unique');

            // Indexes for efficient querying
            $table->index(['subject_id', 'subject_type']);
            $table->index('action');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_stat_summaries');
    }
};
