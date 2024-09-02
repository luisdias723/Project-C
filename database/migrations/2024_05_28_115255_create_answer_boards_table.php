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
        Schema::create('answer_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id');
            $table->foreign('answer_id')->references('id')->on('answers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('board_id');
            $table->foreign('board_id')->references('id')->on('quadros')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_boards');
    }
};
