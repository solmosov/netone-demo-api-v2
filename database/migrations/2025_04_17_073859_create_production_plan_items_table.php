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
        Schema::create('production_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('production_plans');
            $table->foreignId('depart_id')->constrained('departs');
            $table->foreignId('recipe_item_id')->constrained('recipe_items');
            $table->foreignId('depart_status_id')->constrained('depart_statuses');
            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('amount_type_id')->constrained('amount_types');
            $table->decimal('amount', 15, 2);
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_items');
    }
};
