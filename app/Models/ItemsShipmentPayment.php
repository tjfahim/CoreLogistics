<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsShipmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'items_shipment_cost_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
        'paid_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function itemsShipmentCost()
    {
        return $this->belongsTo(ItemsShipmentCost::class);
    }

    // Automatically update parent cost status when payment is saved
    protected static function booted()
    {
        static::saved(function ($payment) {
            $payment->itemsShipmentCost->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            $payment->itemsShipmentCost->updatePaymentStatus();
        });
    }
}