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
            $table->foreign('depart_user_id')->references('id')->on('users');

            $table->foreignId('status_id')->constrained('statuses');
            $table->boolean('is_sending_depart');
            $table->string('comment')->nullable();
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
