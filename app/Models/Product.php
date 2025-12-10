<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_description',
        'weight',
        'price',
        'customer_id',
        'status',
    ];

    // Each product belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Many-to-many with boxes
    public function boxes()
    {
        return $this->belongsToMany(Box::class)
                    ->withPivot('weight_in_box')
                    ->withTimestamps();
    }
}
