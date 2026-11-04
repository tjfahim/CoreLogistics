<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Extra charge billed to one customer inside one shipment (fixed amount, or rate per kg of his final weight). */
class CustomerExtraBill extends Model
{
    protected $fillable = ['items_shipment_id', 'customer_id', 'title', 'bill_type', 'amount', 'rate_per_kg', 'bill_date', 'note', 'user_id'];
    protected $casts = ['bill_date' => 'date', 'amount' => 'float', 'rate_per_kg' => 'float'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function itemshipment() { return $this->belongsTo(ItemsShipment::class, 'items_shipment_id'); }

    /** Amount of this bill for a given billing weight (kg of the customer in that shipment). */
    public function amountFor(float $kg): float
    {
        return $this->bill_type === 'per_kg'
            ? round((float) $this->rate_per_kg * $kg, 2)
            : round((float) $this->amount, 2);
    }
}
