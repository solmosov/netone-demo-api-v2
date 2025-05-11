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
        Schema::create('producer_product_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained('users');
            $table->foreignId('depart_id')->constrained('departs');
            $table->unsignedBigInteger('depart_user_id')->nullable();
            $table->foreignId('depart_user_id')->constrained('depart_user_id');
            $table->foreignId('status_id')->constrained('statuses');
            $table->boolean('is_sending');
            $table->string('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producer_product_trans');
    }
};
