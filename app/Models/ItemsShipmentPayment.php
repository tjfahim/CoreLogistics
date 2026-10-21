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
        'paid_by',
        'user_id',                  // system user who saved this payment
        'shipment_bill_payment_id', // voucher (multi-shipment payment) this line belongs to
        'currency', 'foreign_amount', 'exchange_rate',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function itemsShipmentCost()
    {
        return $this->belongsTo(ItemsShipmentCost::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(ShipmentBillPayment::class, 'shipment_bill_payment_id');
    }

    // Keep the parent cost (and its voucher total) in sync
    protected static function booted()
    {
        static::saved(function ($payment) {
            $payment->itemsShipmentCost->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            if ($payment->itemsShipmentCost) {
                $payment->itemsShipmentCost->updatePaymentStatus();
            }

            // a single line removed from a voucher -> shrink the voucher (or drop it when empty)
            if ($payment->shipment_bill_payment_id) {
                ShipmentBillPayment::find($payment->shipment_bill_payment_id)?->syncTotal();
            }
        });
    }
}
