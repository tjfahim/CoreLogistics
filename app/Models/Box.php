<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;

    protected $fillable = [
        'box_code',
        'total_weight',
        'status',
    ];

    // Many-to-many with products
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('weight_in_box')
                    ->withTimestamps();
    }
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
