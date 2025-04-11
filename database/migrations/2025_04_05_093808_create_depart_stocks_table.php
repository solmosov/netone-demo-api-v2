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
        Schema::create('depart_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depart_id')->constrained('departs')->cascadeOnDelete();
            $table->foreignId('polka_id')->constrained('polkas');
            $table->foreignId('amount_type_id')->constrained('amount_types');
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depart_stocks');
    }
};
