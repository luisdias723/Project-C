<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Freguesia;
use App\Models\Traje;
use App\Models\Quadro;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $domain =  env('APP_ENV') == 'local' ? 'hovo' : 'vianafestas';

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'info@'.$domain.'.pt',
            'phone' => '912345678',
            'password' => Hash::make('teste@22'),
            'register_completed' => 1,
            'category_id' => 1
        ]);
        $question_1 = Question::create([
            'description' => 'Nome Completo',
            'type_question' => 'text',
            'isMultiple' => 0,
        ]);
        $question_2 = Question::create([
            'description' => 'Data de Nascimento',
            'type_question' => 'date',
            'isMultiple' => 0,
        ]);
        $question_1 = Question::create([
            'description' => 'Nome Responsável',
            'type_question' => 'text',
            'isMultiple' => 0,
        ]);
        $question_5 = Question::create([
            'description' => 'Telefone/Telemóvel Responsável',
            'type_question' => 'phone',
            'num_digits' => 9,
            'isMultiple' => 0,
        ]);
        $question_7 = Question::create([
            'description' => 'Email Responsável',
            'type_question' => 'email',
            'num_digits' => 9,
            'isMultiple' => 0,
        ]);
        $question_5 = Question::create([
            'description' => 'Nº CC Responsável',
            'type_question' => 'number',
            'num_digits' => 9,
            'isMultiple' => 0,
        ]);
        $question_7 = Question::create([
            'description' => 'Email',
            'type_question' => 'email',
            'num_digits' => 9,
            'isMultiple' => 0,
        ]);
        $question_5 = Question::create([
            'description' => 'Telefone/Telemóvel',
            'type_question' => 'phone',
            'num_digits' => 9,
            'isMultiple' => 0,
        ]);
        $question_3 = Question::create([
            'description' => 'País',
            'type_question' => 'country',
            'isMultiple' => 0,
        ]);
        $question_4 = Question::create([
            'description' => 'Distrito',
            'type_question' => 'district',
            'isMultiple' => 0,
        ]);
        $question_4 = Question::create([
            'description' => 'Concelho',
            'type_question' => 'concelho',
            'isMultiple' => 0,
        ]);
        $question_4 = Question::create([
            'description' => 'Freguesia',
            'type_question' => 'freguesia',
            'isMultiple' => 0,
        ]);
        $question_5 = Question::create([
            'description' => 'Nº CC',
            'type_question' => 'number',
            'num_digits' => 9,
            'isMultiple' => 0,
        ]);
        $question_5 = Question::create([
            'description' => 'Nº Passaporte',
            'type_question' => 'number',
            'num_digits' => 12,
            'isMultiple' => 0,
        ]);
        $question_6 = Question::create([
            'description' => 'Traje',
            'type_question' => 'select',
            'isMultiple' => 0,
        ]);
        $question_6 = Question::create([
            'description' => 'Fotografia(s)',
            'type_question' => 'image',
            'isMultiple' => 0,
        ]);
        $question_6 = Question::create([
            'description' => 'Membro de Grupo Folclórico',
            'type_question' => 'checkbox',
            'isMultiple' => 0,
        ]);
        $question_1 = Question::create([
            'description' => 'Nome do Grupo',
            'type_question' => 'rancho',
            'isMultiple' => 0,
        ]);
        $question_6 = Question::create([
            'description' => 'Observações',
            'type_question' => 'textarea',
            'isMultiple' => 0,
        ]);

        DB::table('registration_statuses')
            ->insert([
                'description' => 'Em análise',
            ]);

        DB::table('registration_statuses')
            ->insert([
                'description' => 'Rejeitada',
            ]);

        DB::table('registration_statuses')
            ->insert([
                'description' => 'Aprovada',
            ]);

        $this->call(zoneSeeder::class);
        $this->call(countrySeeder::class);
        $this->call(phoneSeeder::class);
        $this->call(TrajesSeeder::class);
        $this->call(BoardsSeeder::class);
        $this->call(FreguesiasSeeder::class);
        $this->call(ConcelhosSeeder::class);
    }
}
