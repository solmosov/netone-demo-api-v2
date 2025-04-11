<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusDepartTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create(['name' => "Yuk borildi", "code" => "departSendProduct"]);
        Status::create(['name' => "Bekor qilindi", "code" => "departCancelProduct"]);
        Status::create(['name' => "Qabul qilindi", "code" => "departGetProduct"]);
    }
}
