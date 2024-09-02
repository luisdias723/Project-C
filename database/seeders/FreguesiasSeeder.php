<?php

namespace Database\Seeders;

use App\Models\Freguesia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FreguesiasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $freguesias = [
            'Afife',
            'Alvarães',
            'Amonde',
            'Anha',
            'Areosa',
            'Barroselas',
            'Cardielos',
            'Carreço',
            'Carvoeiro',
            'Castelo do Neiva',
            'Chafé',
            'Darque',
            'Deão',
            'Deocriste',
            'Freixieiro de Soutelo',
            'Geraz do Lima (Santa Leocádia)',
            'Geraz do Lima (Santa Maria)',
            'Lanheses',
            'Mazarefes',
            'Meadela',
            'Meixedo',
            'Monserrate',
            'Montaria',
            'Moreira do Geraz do Lima',
            'Mujães',
            'Neiva',
            'Nogueira',
            'Outeiro',
            'Perre',
            'Portela Susã',
            'Santa Maria Maior',
            'Santa Marta de Portuzelo',
            'Serreleis',
            'Subportela',
            'Torre',
            'Vila de Punhe',
            'Vila Franca',
            'Vila Fria',
            'Vila Mou',
            'Vilar de Murteda'
        ];

        foreach ($freguesias as $freguesia) {
            Freguesia::insert([
                'description' => $freguesia
            ]);
        }
    }
}
