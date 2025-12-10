<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentCost extends Model
{
    use HasFactory;
     protected $fillable = [
        'shipment_id',
        'cost_type_id',
        'agent_id',
        'description',
        'rate_per_kg',
        'total_weight',
        'amount',
        'paid_amount',
        'due_amount',
        'status', // 'pending', 'partial', 'paid'
        'cost_date'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function costType()
    {
        return $this->belongsTo(CostType::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payments()
    {
        return $this->hasMany(ShipmentPayment::class);
    }
}
