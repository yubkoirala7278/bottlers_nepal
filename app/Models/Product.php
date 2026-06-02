<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'sku', 'color_code'];
    
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
    
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    public function getFullNameAttribute()
    {
        return "{$this->name} - {$this->sku}";
    }
}
