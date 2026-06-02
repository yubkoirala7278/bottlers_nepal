<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $table = 'warehouse_locations';

    protected $fillable = ['location_code', 'level', 'height', 'max_depth', 'current_fill'];

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function reservation()
    {
        return $this->hasOne(Reservation::class);
    }

    public function getAvailableSpaceAttribute()
    {
        return $this->max_depth - $this->current_fill;
    }

    public function getOccupiedDepthsAttribute()
    {
        $depths = [];
        foreach ($this->inventory as $inv) {
            if ($inv->depth_positions) {
                $depths = array_merge($depths, json_decode($inv->depth_positions, true));
            }
        }
        return $depths;
    }

    public function getDepthMapAttribute()
    {
        $depths = [];
        for ($i = 1; $i <= $this->max_depth; $i++) {
            $depths[$i] = false;
        }

        foreach ($this->inventory as $inv) {
            $positions = $inv->depth_positions ?: [];
            foreach ($positions as $pos) {
                $depths[$pos] = [
                    'batch' => $inv->batch->batch_number,
                    'product' => $inv->batch->product->name . ' ' . $inv->batch->product->sku
                ];
            }
        }

        return $depths;
    }

    public function getNextAvailableDepthAttribute()
    {
        $occupied = $this->occupied_depths;
        for ($i = $this->max_depth; $i >= 1; $i--) {
            if (!in_array($i, $occupied)) {
                return $i;
            }
        }
        return null;
    }
}
