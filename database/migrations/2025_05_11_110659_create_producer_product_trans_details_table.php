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
        Schema::create('producer_product_trans_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trans_id')->constrained('producer_product_trans');

            $table->unsignedBigInteger('product_stock_id')->nullable();
            $table->foreignId('product_stock_id')->constrained('product_stocks');

            $table->unsignedBigInteger('depart_stock_id')->nullable();
            $table->foreignId('depart_stock_id')->constrained('depart_stocks');

            $table->foreignId('amount_type_id');
            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producer_product_trans_details');
    }
};
