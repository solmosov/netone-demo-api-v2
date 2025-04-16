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
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('production_recipes');
            $table->foreignId('amount_type_id')->constrained('amount_types');
            $table->foreignId('status_id')->constrained('statuses');
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->string('comment')->nullable();
            $table->timestamp('finish_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
