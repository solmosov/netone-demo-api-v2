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
        Schema::table('depart_stocks', function (Blueprint $table) {
            $table->decimal('min_amount')->default(0);
            $table->decimal('max_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depart_stocks', function (Blueprint $table) {
            $table->dropColumn(['min_amount','max_amount']);
        });
    }
};
