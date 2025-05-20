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
        Status::create(["name" => "Jarayonda", "code" => "productionPlanProcess"]);
        Status::create(["name" => "Bekor qilindi", "code" => "productionPlanCancel"]);
        Status::create(["name" => "Tayyor", "code" => "productionPlanCompleted"]);
    }
}
