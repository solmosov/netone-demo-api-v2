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
        Schema::table('recipe_items', function (Blueprint $table) {
            $table->dropForeign('recipe_items_production_recipe_id_foreign');

            $table->foreign('production_recipe_id')
                ->references('id')
                ->on('production_recipes')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_items', function (Blueprint $table) {

            $table->dropForeign('recipe_items_production_recipe_id_foreign');

            $table->foreign('product_recipe_id')
                ->references('id')
                ->on('production_recipes');
        });
    }
};
