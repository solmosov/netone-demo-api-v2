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
        Schema::table('production_process', function (Blueprint $table) {
            $table->unsignedBigInteger('depart_id')->nullable();
            $table->foreign('depart_id')->references('id')->on('departs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_process', function (Blueprint $table) {
            $table->dropForeign(['depart_id']);
            $table->dropColumn('depart_id');
        });
    }
};
