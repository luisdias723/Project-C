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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->default('/storage/avatars/1653993172_user.png');

            $table->string('mobile_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('type', 30)->nullable();

            $table->string('birthday')->nullable();
            $table->string('address')->nullable();
            $table->string('zipcode', 50)->nullable();
            $table->string('city')->nullable();
            $table->string('country', 50)->nullable();


            $table->string('nif', 50)->nullable();
            $table->string('iban', 255)->nullable();

            $table->boolean('register_completed')->default(0);
            $table->boolean('isMobile')->default(1);

            $table->text('available_hours')->default(serialize([
                array('value'=> 1, 'name'=> 'Segunda-feira', 'active'=> false, 'start'=> '', 'end'=> ''),
                array('value'=> 2, 'name'=> 'Terça-feira', 'active'=> false, 'start'=> '', 'end'=> ''),
                array('value'=> 3, 'name'=> 'Quarta-feira', 'active'=> false, 'start'=> '', 'end'=> ''),
                array('value'=> 4, 'name'=> 'Quinta-feira', 'active'=> false, 'start'=> '', 'end'=> ''),
                array('value'=> 5, 'name'=> 'Sexta-feira', 'active'=> false, 'start'=> '', 'end'=> ''),
                array('value'=> 6, 'name'=> 'Sábado', 'active'=> false, 'start'=> '', 'end'=> ''),
                array('value'=> 7, 'name'=> 'Domingo', 'active'=> false, 'start'=> '', 'end'=> ''),
              ]));

            $table->text('colors')->nullable();
              
            $table->boolean('is_mastermind')->default(0);

            $table->boolean('same_invoice_data')->default(false);
            $table->string('invoice_name')->nullable();
            $table->string('invoice_nif', 50)->nullable();
            $table->string('invoice_iban', 255)->nullable();
            $table->string('invoice_address')->nullable();

            $table->string('tax_regime', 10)->nullable();

            $table->boolean('active')->default(1);
            $table->timestamp('TermsPolicyAccepted')->nullable();

            $table->boolean('coach_coachee')->default(0);

            $table->string('category_id');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
