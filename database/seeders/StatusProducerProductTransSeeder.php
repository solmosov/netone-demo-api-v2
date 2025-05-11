<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusProducerProductTransSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create(['name' => "Yuk yuborildi", "code" => "producerSendProduct"]);
        Status::create(['name' => "Bekor qilindi", "code" => "producerCancelProduct"]);
        Status::create(['name' => "Qabul qilindi", "code" => "producerReceiveProduct"]);
    }
}
