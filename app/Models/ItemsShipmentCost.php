<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsShipmentCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'items_shipment_id',
        'cost_type_id',
        'agent_id',
        'description',
        'rate_per_kg',
        'total_weight',
        'amount',
        'paid_amount',
        'due_amount',
        'status',
        'cost_date'
    ];

    protected $casts = [
        'cost_date' => 'date',
    ];

    public function itemsShipment()
    {
        return $this->belongsTo(ItemsShipment::class);
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
        return $this->hasMany(ItemsShipmentPayment::class);
    }

    // Update payment status when payment is made
    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->due_amount = $this->amount - $totalPaid;

        if ($totalPaid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }
}