<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One voucher paying cost lines of one or many shipments in a single go.
 * The split is stored as ItemsShipmentPayment rows (shipment_bill_payment_id).
 */
class ShipmentBillPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'user_id',
        'agent_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payments()
    {
        return $this->hasMany(ItemsShipmentPayment::class, 'shipment_bill_payment_id');
    }

    /** Recalculate the voucher total from its lines; remove the voucher when no line is left. */
    public function syncTotal(): void
    {
        $total = (float) $this->payments()->sum('amount');

        if ($total <= 0) {
            $this->delete();
            return;
        }

        $this->amount = $total;
        $this->save();
    }
}
