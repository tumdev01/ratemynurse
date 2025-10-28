<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->unsignedBigInteger('rateable_id')->nullable();
            $table->string('rateable_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn(['rateable_id', 'rateable_type']);
        });
    }
};
