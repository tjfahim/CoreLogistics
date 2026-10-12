<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsShipmentCost extends Model
{
    use HasFactory;

    // key stored in DB => label shown in UI
    public const CURRENCIES = [
        'bdt'   => 'BDT',
        'usd'   => 'USD',
        'rmb'   => 'RMB',
        'dinar' => 'Dinar',
    ];

    protected $fillable = [
        'items_shipment_id',
        'cost_type_id',
        'currency',
        'exchange_rate_bdt',
        'agent_id',
        'description',
        'rate_per_kg',
        'total_weight',
        'amount',            // amount in the selected currency
        'final_bdt_cost',    // amount * exchange_rate_bdt  (this is what paid/due are based on)
        'paid_amount',       // BDT
        'due_amount',        // BDT
        'status',
        'cost_date'
    ];

    protected $casts = [
        'cost_date'         => 'date',
        'exchange_rate_bdt' => 'decimal:4',
        'final_bdt_cost'    => 'decimal:2',
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

    public function attachments()
    {
        return $this->hasMany(ItemsShipmentCostAttachment::class, 'items_shipment_cost_id');
    }

    public function getCurrencyLabelAttribute()
    {
        return self::CURRENCIES[$this->currency] ?? strtoupper((string) $this->currency);
    }

    // Update payment status when payment is made (payments are always in BDT)
    public function updatePaymentStatus()
    {
        $totalBdt  = (float) ($this->final_bdt_cost ?? $this->amount);
        $totalPaid = (float) $this->payments()->sum('amount'); // fresh query, no stale relation

        $this->paid_amount = $totalPaid;
        $this->due_amount  = $totalBdt - $totalPaid;

        if ($totalPaid >= $totalBdt) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }
}