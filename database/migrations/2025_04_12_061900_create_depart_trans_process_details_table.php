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
        Schema::create('depart_trans_process_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amount_type_id')->constrained('amount_types');
            $table->foreignId('depart_trans_process_id')->constrained('depart_trans_processes');
            $table->foreignId('sender_depart_stock_id')->constrained('depart_stocks');
            $table->unsignedBigInteger('receive_depart_stock_id')->nullable();
            $table->foreign('receive_depart_stock_id')->references('id')->on('depart_stocks');
            $table->decimal('amount', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depart_trans_process_details');
    }
};
