<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Batch;
use Carbon\Carbon;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {

            // Create 3 batches for each product
            for ($i = 1; $i <= 3; $i++) {

                $productionDate = Carbon::now()->subDays(rand(1, 365));

                Batch::create([
                    'product_id'      => $product->id,
                    'batch_number'    => 'BATCH-' . $product->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'production_date' => $productionDate,
                    'expiry_date'     => $productionDate->copy()->addYear(),
                ]);
            }
        }
    }
}
