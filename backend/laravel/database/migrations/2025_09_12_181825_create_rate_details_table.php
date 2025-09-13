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
        Schema::create('rate_details', function (Blueprint $table) {
            $table->id();
            $table->foreign('rate_id')->references('id')->on('rates')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->integer('scores')->default(1);
            $table->string('scores_for');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_details');
    }
};
