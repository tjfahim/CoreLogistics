<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPayment extends Model
{
    use HasFactory;
      protected $fillable = [
        'shipment_id',
        'agent_id',
        'payment_type',
        'rate_per_kg',
        'total_weight',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status'
    ];

    protected $casts = [
        'rate_per_kg' => 'decimal:2',
        'total_weight' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Update payment status when paid_amount changes
    public function updatePaymentStatus()
    {
        if ($this->due_amount <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        $this->save();
    }
}
