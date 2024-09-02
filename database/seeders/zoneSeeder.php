<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\District;

class zoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        District::insert([
            'description' => 'Açores'
        ]);
         District::insert([
            'description' => 'Aveiro'
        ]);
        District::insert([
            'description' => 'Beja'
        ]);
        District::insert([
            'description' => 'Braga'
        ]);
        District::insert([
            'description' => 'Bragança'
        ]);
        District::insert([
            'description' => 'Castelo Branco'
        ]);
        District::insert([
            'description' => 'Coimbra'
        ]);
        District::insert([
            'description' => 'Évora'
        ]);
        District::insert([
            'description' => 'Faro'
        ]);
        District::insert([
            'description' => 'Guarda'
        ]);
        District::insert([
            'description' => 'Leiria'
        ]);
        District::insert([
            'description' => 'Lisboa'
        ]);
        District::insert([
            'description' => 'Madeira'
        ]);
        District::insert([
            'description' => 'Portalegre'
        ]);
        District::insert([
            'description' => 'Porto'
        ]);
        District::insert([
            'description' => 'Santarém'
        ]);
        District::insert([
            'description' => 'Setúbal'
        ]);
        District::insert([
            'description' => 'Viana do Castelo'
        ]);
        District::insert([
            'description' => 'Vila Real'
        ]);
        District::insert([
            'description' => 'Viseu'
        ]);
    }
}
