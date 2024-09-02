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
        Schema::create('cortejo_participants', function (Blueprint $table) {
            $table->id();
            $table->string('participant');
            $table->foreignId('question_id');
            $table->foreign('question_id')->references('id')->on('questions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('registration_id');
            $table->foreign('registration_id')->references('id')->on('registrations')->onUpdate('cascade')->onDelete('cascade');
            $table->string('answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortejo_participants');
    }
};
