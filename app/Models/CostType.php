<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'type', // 'fixed', 'per_kg'
        'have_agent',
        'have_partial_payment',
        
    ];
protected $casts = [
    'cost_date' => 'date',
];
    public function shipmentCosts()
    {
        return $this->hasMany(ShipmentCost::class);
}
}
