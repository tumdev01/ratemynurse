<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nursing_profiles', function (Blueprint $table) {
            $table->text('medical_condition_detail')->nullable()->after('medical_condition');
            $table->text('history_of_drug_allergy_detail')->nullable()->after('history_of_drug_allergy');
        });
    }

    public function down(): void
    {
        Schema::table('nursing_profiles', function (Blueprint $table) {
            $table->dropColumn(['medical_condition_detail', 'history_of_drug_allergy_detail']);
        });
    }
};
