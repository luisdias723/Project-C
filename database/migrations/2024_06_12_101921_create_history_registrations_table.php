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
        Schema::create('history_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id');
            $table->foreign('registration_id')->references('id')->on('registrations')->onUpdate('cascade')->onDelete('cascade');
            $table->text('description');
            $table->boolean('isObs')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_registrations');
    }
};
