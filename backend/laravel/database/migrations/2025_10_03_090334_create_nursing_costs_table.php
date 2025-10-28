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
        Schema::create('nursing_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // DAILY , MONTHLY
            $table->string('hire_rule'); // ลักษณะการจ้าง ชั่วคราว ไปกลับ ชั่วคราวค้างคืน ...
            $table->string('name'); // Text ที่จะแสดง
            $table->text('description'); // รายละเอียด
            $table->double('cost')->default(0);;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_costs');
    }
};
