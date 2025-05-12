<?php

namespace Database\Seeders;

use App\Models\Polka;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PolkaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Polka::create(['name' => "Polka A"]);
        Polka::create(['name' => "Polka B"]);
        Polka::create(['name' => "Polka C"]);
        Polka::create(['name' => "Polka D"]);
        Polka::create(['name' => "Polka F"]);
    }
}
