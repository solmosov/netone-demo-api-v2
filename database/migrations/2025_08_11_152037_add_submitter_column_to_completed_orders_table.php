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
        Schema::table('completed_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('submitter_id')->nullable();
            $table->foreign('submitter_id')->references('id')->on('users');
            $table->timestamp('submitted_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('completed_orders', function (Blueprint $table) {
            $table->dropForeign(['submitter_id']);
            $table->dropColumn(['submitter_id', 'submitted_date']);
        });
    }
};
