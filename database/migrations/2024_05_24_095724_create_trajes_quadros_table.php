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
        Schema::create('trajes_quadros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id');
            $table->foreign('board_id')->references('id')->on('quadros')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('traje_id');
            $table->foreign('traje_id')->references('id')->on('trajes')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trajes_quadros');
    }
};
