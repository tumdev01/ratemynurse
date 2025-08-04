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
        Schema::create('nursing_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->string('religion');
            $table->text('about')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->default('THAI');
            $table->double('cost')->default(0.00);
            $table->foreignId('sub_district_id')->nullable()
                ->references('id')->on('sub_districts')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('district_id')->nullable()
                ->references('id')->on('districts')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('province_id')->nullable()
                ->references('id')->on('provinces')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->string('zipcode');
            $table->boolean('certified')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_profiles');
    }
};
