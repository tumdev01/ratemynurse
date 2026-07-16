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
        Schema::create('user_subscription_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_request_id');
            $table->foreign('subscription_request_id')->references('id')->on('user_subscription_requests')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->string('action'); // submitted, awaiting_payment, payment_accepted
            $table->string('performed_by')->nullable(); // user or admin
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscription_logs');
    }
};
