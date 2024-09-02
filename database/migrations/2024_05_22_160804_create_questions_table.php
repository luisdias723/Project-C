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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->string('type_question')->nullable();
            $table->integer('num_digits')->default(2);
            $table->boolean('isMultiple')->default(0);
            $table->boolean('isSpecial')->default(0);
            $table->boolean('isSpecialBoards')->default(0);
            $table->boolean('isTraje')->default(0);
            $table->boolean('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
