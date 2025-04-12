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
        Schema::create('depart_trans_process_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depart_trans_process_id')->constrained('depart_trans_processes');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('polka_id')->constrained('polkas');
            $table->unsignedBigInteger('received_polka_id')->nullable();
            $table->foreign('received_polka_id')->references('id')->on('polkas');
            $table->foreignId('amount_type_id')->constrained('amount_types');
            $table->decimal('amount', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depart_trans_process_detail');
    }
};
