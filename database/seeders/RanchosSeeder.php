<?php

namespace Database\Seeders;

use App\Models\Rancho;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RanchosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranchos = [
            'Rancho 1',
            'Rancho 2',
            'Rancho 3',
        ];

        foreach ($ranchos as $rancho) {
            Rancho::insert([
                'description' => $rancho
            ]);
        }
    }
}
