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
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('name');
            $table->string('about')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('cardid');
            $table->foreignId('sub_district_id')->nullable()
                ->references('id')->on('sub_districts')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('district_id')->nullable()
                ->references('id')->on('districts')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('province_id')->nullable()
                ->references('id')->on('provinces')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->string('zipcode')->nullable();

            // Contact person
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('contact_person_relation')->nullable();

            $table->json('services_required')->nullable();

            $table->boolean('privacy')->default(0);
            $table->boolean('policy')->default(0);
            $table->boolean('newsletter')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
