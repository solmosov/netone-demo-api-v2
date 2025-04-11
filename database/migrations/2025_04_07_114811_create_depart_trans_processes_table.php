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
        Schema::create('depart_trans_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('trans_perm_id')->constrained('trans_perms');

            $table->foreignId('sender_depart_id')->constrained('departs');
            $table->foreignId('receiver_depart_id')->constrained('departs');

            $table->foreignId('sender_user_id')->constrained('users');
            $table->unsignedBigInteger('receiver_user_id')->nullable();
            $table->foreign('receiver_user_id')->references('id')->on('users');

            $table->foreignId('status_id');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depart_trans_processes');
    }
};
