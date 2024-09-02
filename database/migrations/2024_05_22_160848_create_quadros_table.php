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
        Schema::create('quadros', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->boolean('active')->default(1);
            $table->integer('insc_limit')->default(0);
            $table->integer('total_insc')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quadros');
    }
};
