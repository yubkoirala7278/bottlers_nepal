<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WarehouseLocation;

class WarehouseLocationSeeder extends Seeder
{
    public function run()
    {
        $levels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        foreach ($levels as $level) {
            for ($height = 1; $height <= 6; $height++) {
                $locationCode = $level . $height;
                WarehouseLocation::create([
                    'location_code' => $locationCode,
                    'level' => $level,
                    'height' => $height,
                    'max_depth' => 50,
                    'current_fill' => 0,
                ]);
            }
        }
    }
}
