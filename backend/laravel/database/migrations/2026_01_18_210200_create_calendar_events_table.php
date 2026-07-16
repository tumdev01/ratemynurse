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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('member_contact_id')->nullable();

            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('member_profile_id');

            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('provider_profile_id');

            $table->string('event_type');
            // accepted | cancelled | rescheduled | manual

            $table->string('title');

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->string('status')->default('active');
            // active | completed | cancelled

            $table->index(['provider_id', 'start_date']);
            $table->index(['member_id', 'start_date']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
