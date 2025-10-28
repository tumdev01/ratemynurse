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
        Schema::create('nursing_home_license_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->foreign('profile_id')->references('id')->on('nursing_home_profiles')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->string('filename');
            $table->string('filetype');
            $table->string('path');
            $table->string('fullpath');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_home_license_images');
    }
};
