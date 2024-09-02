<?php

namespace Database\Seeders;

use App\Models\Concelho;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConcelhosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $concelhos = [
            'Viana do Castelo',
            'Ponte de Lima',
            'Arcos de Valdevez',
            'Monção',
            'Caminha',
            'Ponte da Barca',
            'Valença',
            'Vila Nova de Cerveira',
            'Paredes de Coura',
            'Melgaço'
        ];

        foreach ($concelhos as $concelho) {
            Concelho::insert([
                'description' => $concelho
            ]);
        }
    }
}
