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
        Schema::create('nursing_cvs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('graducated');
            $table->string('edu_ins');
            $table->integer('graducated_year');
            $table->double('gpa');
            $table->string('cert_no');
            $table->date('cert_date');
            $table->date('cert_expire');
            $table->text('cert_etc');
            $table->text('extra_courses');
            $table->string('current_workplace');
            $table->string('department');
            $table->string('position');
            $table->integer('exp');
            $table->string('work_type');
            $table->string('extra_shirft');
            $table->string('languages');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_cvs');
    }
};
