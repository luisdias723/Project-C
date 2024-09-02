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
        Schema::create('history_reg_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hist_id');
            $table->foreign('hist_id')->references('id')->on('history_registrations')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_reg_users');
    }
};
