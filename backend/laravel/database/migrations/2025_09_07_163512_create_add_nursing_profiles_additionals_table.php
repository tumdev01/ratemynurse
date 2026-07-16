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
        Schema::table('nursing_profiles', function (Blueprint $table) {
            $table->string('description')->nullable()->after('about');
            $table->integer('exp_year')->default(0);
            $table->string('work_style')->nullable()->default(NULL);
            $table->json('skill')->nullable()->default(NULL);
            $table->json('service_packages')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nursing_profiles', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('exp_year');
            $table->dropColumn('work_style');
            $table->dropColumn('skill');
            $table->dropColumn('service_packages');
        });
    }
};
