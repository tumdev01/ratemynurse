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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();

            // คนกด (MEMBER)
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // profile ที่ถูก favorite
            $table->morphs('profile'); 
            // profile_id, profile_type

            $table->timestamps();

            // ป้องกันกดซ้ำ
            $table->unique([
                'user_id',
                'profile_id',
                'profile_type'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
