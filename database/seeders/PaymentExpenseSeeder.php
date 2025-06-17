<?php

namespace Database\Seeders;

use Database\Factories\PaymentExpenseFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentExpenseFactory::new()->count(1000)->create();
    }
}
