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
        Schema::create('nusing_home_room_images', function (Blueprint $table) {
            $table->id();
            $table->string('path'); // path of image
            $table->string('room_id'); // group of images [ nursing_home_gallery, nursing_gallery, nursing_home_room]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nusing_home_room_images');
    }
};
