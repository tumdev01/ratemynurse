<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_contacts', function (Blueprint $table) {
            $table->id();

            // ผู้จอง (member)
            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // เจ้าของ profile (provider user)
            $table->unsignedBigInteger('provider_user_id');
            $table->foreign('provider_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // provider profile (morph)
            $table->unsignedBigInteger('provider_profile_id');
            $table->string('provider_type'); // App\Models\NursingProfile | NursingHomeProfile

            // ประเภท contact
            $table->string('type')->default('USER');

            // ข้อมูลการติดต่อ
            $table->longText('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->string('facebook')->nullable();
            $table->string('lineid')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ป้องกันการสร้าง contact ซ้ำแบบ logic ถูกต้อง
            $table->unique([
                'member_id',
                'provider_profile_id',
                'provider_type',
                'start_date',
                'end_date',
            ], 'unique_member_provider_profile_date');

            // index สำหรับ query
            $table->index(['member_id', 'created_at']);
            $table->index(['provider_user_id']);
            $table->index(['provider_profile_id', 'provider_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_contacts');
    }
};
