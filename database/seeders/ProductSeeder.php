<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Coke', 'sku' => '250ml', 'color_code' => '#C8102E'],
            ['name' => 'Coke', 'sku' => '175ml', 'color_code' => '#C8102E'],
            ['name' => 'Coke', 'sku' => '2250ml', 'color_code' => '#C8102E'],
            ['name' => 'Coke', 'sku' => '1500ml', 'color_code' => '#C8102E'],
            ['name' => 'Coke', 'sku' => '1000ml', 'color_code' => '#C8102E'],

            ['name' => 'Fanta', 'sku' => '250ml', 'color_code' => '#FF8300'],
            ['name' => 'Fanta', 'sku' => '175ml', 'color_code' => '#FF8300'],
            ['name' => 'Fanta', 'sku' => '2250ml', 'color_code' => '#FF8300'],
            ['name' => 'Fanta', 'sku' => '1500ml', 'color_code' => '#FF8300'],
            ['name' => 'Fanta', 'sku' => '1000ml', 'color_code' => '#FF8300'],

            ['name' => 'Sprite', 'sku' => '250ml', 'color_code' => '#009639'],
            ['name' => 'Sprite', 'sku' => '175ml', 'color_code' => '#009639'],
            ['name' => 'Sprite', 'sku' => '2250ml', 'color_code' => '#009639'],
            ['name' => 'Sprite', 'sku' => '1500ml', 'color_code' => '#009639'],
            ['name' => 'Sprite', 'sku' => '1000ml', 'color_code' => '#009639'],

            ['name' => 'Kinley Soda', 'sku' => '250ml', 'color_code' => '#00AEEF'],

            ['name' => 'Fanta Lemon', 'sku' => '2250ml', 'color_code' => '#FFD100'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
