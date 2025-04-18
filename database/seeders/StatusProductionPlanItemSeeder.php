<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusProductionPlanItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create(['name' => "Yangi", 'code' => 'productionPlanDepartNew']);
        Status::create(['name' => "Qabul qilindi", 'code' => 'productionPlanDepartReceived']);
        Status::create(['name' => "Tayyorlandi", 'code' => 'productionPlanDepartCompleted']);
    }
}
