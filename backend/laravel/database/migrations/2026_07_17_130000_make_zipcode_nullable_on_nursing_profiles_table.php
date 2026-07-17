<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// ใช้ raw SQL แทน Schema::table()->change() เพราะโปรเจกต์นี้ไม่ได้ติดตั้ง doctrine/dbal
// (จำเป็นสำหรับ ->change() ใน Laravel 10)
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE nursing_profiles MODIFY zipcode VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE nursing_profiles MODIFY zipcode VARCHAR(255) NOT NULL DEFAULT ''");
    }
};
