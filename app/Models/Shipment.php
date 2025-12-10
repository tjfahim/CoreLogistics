<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_code',
        'agent_id',
        'cnf_agent_id',
        'total_weight',
        'status'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function cnfAgent()
    {
        return $this->belongsTo(Agent::class, 'cnf_agent_id');
    }

    public function boxes()
    {
        return $this->belongsToMany(Box::class, 'shipment_boxes');
    }

    public function costs()
    {
        return $this->hasMany(ShipmentCost::class);
    }

    public function getTotalCostAttribute()
    {
        return $this->costs->sum('amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->costs->sum('paid_amount');
    }

    public function getTotalDueAttribute()
    {
        return $this->costs->sum('due_amount');
    }
    public function customerBills()
    {
        return $this->hasMany(CustomerBill::class, 'shipment_id', 'id');
    }
}