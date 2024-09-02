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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('status_id');
            $table->foreign('status_id')->references('id')->on('registration_statuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('formulario_id');
            $table->foreign('formulario_id')->references('id')->on('formularios')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('active')->default(1);
            $table->text('email');
            $table->text('qrCode')->nullable();
            $table->text('ticket')->nullable();
            $table->text('edit')->nullable();
            $table->text('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
