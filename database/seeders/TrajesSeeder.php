<?php

namespace Database\Seeders;

use App\Models\Traje;
use Illuminate\Database\Seeder;

class TrajesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trajes = [
            'Traje à Vianesa de Afife Vermelho',
            'Traje à Vianesa de Afife "de Dó"',
            'Taje à Vianesa de Areosa',
            'Traje à Vianesa Vermelho da Ribeira do Lima',
            'Traje à Vianesa de Carreço Vermelho',
            'Traje à Vianesa "de Dó" da Ribeira do Lima',
            'Traje à Vianesa de Carreço "de Dó"',
            'Traje à Vianesa das Terras de Geraz',
            "Traje à Vianesa Vermelho da Serra d'Arga",
            "Traje à Vianesa 'de Dó' da Serra d'Arga",
            "Traje à Vianesa Azulão da Serra d'Arga",
            "Traje à Vianesa Verde da Serra d'Arga",
            'Traje à Vianesa de Freixieiro de Soutelo',
            'Traje de Domingar de Barra Vermelho',
            'Traje de Domingar de Barra Preta',
            'Traje de Domingar de Barra Azulão',
            'Traje de Domingar de Barra Azul-Marinho',
            'Traje de Mordoma Preto com Colete e Palmito',
            'Traje de Mordoma Preto com Colete e Vela Votiva',
            'Traje de Mordoma Azul com Colete e Palmito',
            'Traje de Mordoma Azul com Colete e Vela Votiva',
            'Traje de Mordoma Preto com Casaca e Palmito',
            'Traje de Mordoma Preto com Casaca e Vela Votiva',
            'Traje de Mordoma de Santa Marta de Portuzelo',
            'Traje de Cerimónia/Morgada',
            'Traje de Noiva',
            'Traje de Festa da Ribeira'
        ];
        foreach ($trajes as $traje) {
            Traje::insert([
                'description' => $traje,
            ]);
        }
    }
}
