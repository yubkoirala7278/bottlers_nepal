<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'batch_number', 'production_date', 'expiry_date'];

    protected $casts = [
        'production_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public static function calculateExpiryDate($productSku, $productionDate)
    {
        $days = 90; // default

        // Parse SKU to get volume in ml
        preg_match('/(\d+)/', $productSku, $matches);
        $volume = isset($matches[1]) ? (int)$matches[1] : 0;

        if (in_array($volume, [2250, 1500])) {
            $days = 90;
        } elseif ($volume == 1000) {
            $days = 75;
        } elseif (in_array($volume, [250, 175])) {
            $days = 180;
        }

        return Carbon::parse($productionDate)->addDays($days);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->inventory->sum('quantity');
    }
}
