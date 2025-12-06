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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('subscribable_id')->nullable()->after('id');
            $table->string('subscribable_type')->nullable()->after('subscribable_id');
            $table->index(['subscribable_id', 'subscribable_type']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['subscribable_id', 'subscribable_type']);
            $table->dropColumn(['subscribable_id', 'subscribable_type']);
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
