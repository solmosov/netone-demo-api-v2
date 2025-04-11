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
        Status::created(['name' => "Yuk borildi", "code" => "departSendProduct"]);
        Status::created(['name' => "Bekor qilindi", "code" => "departCancelProduct"]);
        Status::created(['name' => "Qabul qilindi", "code" => "departGetProduct"]);
    }
}
