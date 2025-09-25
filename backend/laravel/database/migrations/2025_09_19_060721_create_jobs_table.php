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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('service_type');
            $table->string('hire_type');
            $table->double('cost');
            $table->date('start_date');
            $table->text('description');
            $table->string('address');
            $table->integer('province_id');
            $table->integer('district_id');
            $table->integer('sub_district_id');
            $table->string('phone');
            $table->string('email')->nullable()->default(NULL);
            $table->string('facebook')->nullable()->default(NULL);
            $table->string('lineid')->nullable()->default(NULL);
            $table->string('status')->default('OPEN');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
