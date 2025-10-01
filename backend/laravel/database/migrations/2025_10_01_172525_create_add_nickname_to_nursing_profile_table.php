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
            $table->string('nickname')->nullable()->after('name');
            $table->string('blood')->nullable()->after('nickname');
            $table->string('address')->nullable()->before('province_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nursing_profiles', function (Blueprint $table) {
            $table->dropColumn('nickname');
            $table->dropColumn('blood');
            $table->dropColumn('address');
        });
    }
};
