<?php

namespace Database\Seeders;

use App\Models\Quadro;
use Illuminate\Database\Seeder;

class BoardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quadros = [
            'Vamos para a Romaria',
            'A Romaria',
            'Vamos para a Festa',
            'A Festa',
            'Trajes de Domingar c/ cesta c/ flores',
            'Trajes à Vianesa',
            'Mordomas de Viana',
            'Trajes de Cerimónia',
            'Noivas e Noivos',
            'Ribeira',
        ];
        foreach ($quadros as $quadro) {
            Quadro::insert([
                'description' => $quadro,
            ]);
        }
    }
}
