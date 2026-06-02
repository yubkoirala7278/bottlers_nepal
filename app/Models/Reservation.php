<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    
    protected $fillable = ['warehouse_location_id', 'product_id', 'batch_id', 'reservation_type'];
    
    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}