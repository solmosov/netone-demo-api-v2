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
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->unsignedBigInteger('polka_id')->nullable();
            $table->foreign('polka_id')->references('id')->on('polkas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign(['polka_id']);
            $table->dropColumn('polka_id');

            $table->string('name')->unique();
        });
    }
};
