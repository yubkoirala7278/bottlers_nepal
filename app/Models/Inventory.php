<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    
    protected $table = 'inventory';
    
    protected $fillable = ['batch_id', 'warehouse_location_id', 'quantity', 'depth_positions'];
    
    protected $casts = [
        'depth_positions' => 'array',
    ];
    
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    
    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class);
    }
}