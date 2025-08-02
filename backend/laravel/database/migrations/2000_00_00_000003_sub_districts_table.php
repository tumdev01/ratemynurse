+
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
        Schema::create('sub_districts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);

            $table->unsignedBigInteger('district_id')->nullable();
            $table->foreign('district_id')->references('id')->on('districts')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_districts');
    }
};
