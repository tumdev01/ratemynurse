<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_interviews', function (Blueprint $table) {
            $table->renameColumn('user_id', 'profile_id');
        });

        Schema::table('job_interviews', function (Blueprint $table) {
            $table->double('price')->nullable()->after('description');
            $table->date('start_date')->nullable()->after('price');
            $table->boolean('attach_profile')->default(false)->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('job_interviews', function (Blueprint $table) {
            $table->dropColumn(['price', 'start_date', 'attach_profile']);
        });

        Schema::table('job_interviews', function (Blueprint $table) {
            $table->renameColumn('profile_id', 'user_id');
        });
    }
};
