<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusProductionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create(["name" => "Yangi", "code" => "productionPlanNew"]);
        Status::create(["name" => "Jarayonda", "code" => "productionPlanProcessProcess"]);
        Status::create(["name" => "Bekor qilindi", "code" => "productionPlanProcessCancel"]);
    }
}
