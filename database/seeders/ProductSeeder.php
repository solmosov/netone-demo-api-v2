<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            ["name" => "Tayyor mahsulot 1", 'category_id'=>1, "price_amount_type_id"=>1, "cost_price" => 46000, "sale_price" => 49000, "receive_price"=>0, "updated_at" => now(), "created_at" => now()],
            ["name" => "Tayyor mahsulot 2", 'category_id'=>2, "price_amount_type_id"=>1, "cost_price" => 46000, "sale_price" => 49000, "receive_price"=>0, "updated_at" => now(), "created_at" => now()],
            ["name" => "Tayyor mahsulot 3", 'category_id'=>3, "price_amount_type_id"=>1, "cost_price" => 46000, "sale_price" => 49000, "receive_price"=>0, "updated_at" => now(), "created_at" => now()],
            ["name" => "Tayyor mahsulot 4", 'category_id'=>4, "price_amount_type_id"=>1, "cost_price" => 46000, "sale_price" => 49000, "receive_price"=>0, "updated_at" => now(), "created_at" => now()],
            ["name" => "Tayyor mahsulot 5", 'category_id'=>4, "price_amount_type_id"=>1, "cost_price" => 46000, "sale_price" => 49000, "receive_price"=>0, "updated_at" => now(), "created_at" => now()],
        ]);
    }
}
