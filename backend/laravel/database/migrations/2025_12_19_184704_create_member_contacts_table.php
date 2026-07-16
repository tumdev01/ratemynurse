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
        Schema::create('member_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('provider_role'); // NURSING, NURSING_HOME
            $table->string('provider_type')->nullable(); // เก็บ class name
            $table->string('type')->default('USER'); // เพิ่ม type column
            
            $table->longText('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // ควรเป็น nullable
            
            $table->string('facebook')->nullable();
            $table->string('lineid')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // เพิ่ม unique index เพื่อป้องกันข้อมูลซ้ำ
            $table->unique(
                ['member_id', 'provider_id', 'provider_role'],
                'unique_member_provider_contact'
            );
            
            // เพิ่ม index สำหรับการ query ที่ใช้บ่อย
            $table->index(['member_id', 'created_at']);
            $table->index(['provider_id', 'provider_role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_contacts');
    }
};