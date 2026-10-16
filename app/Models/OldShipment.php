<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldShipment extends Model
{
    protected $fillable = ['name', 'date', 'shipping_mark'];

    protected $casts = ['date' => 'date'];

    public function items()
    {
        return $this->hasMany(OldShipmentItem::class);
    }

    public function bills()
    {
        return $this->hasMany(OldShipmentBill::class);
    }

    public function payments()
    {
        return $this->hasMany(OldPaymentAndReceive::class);
    }

    /** Customers of this shipment (derived from its items). */
    public function customers()
    {
        return OldCustomer::whereIn(
            'id',
            $this->items()->whereNotNull('old_customer_id')->pluck('old_customer_id')
        )->orderBy('name')->get();
    }

    // ---- shipment bill (what we owe for the shipment) ----
    public function getBillTotalAttribute()
    {
        return (float) $this->bills()->sum('total_price');
    }

    public function getPaidTotalAttribute()
    {
        return (float) $this->payments()->where('type', 'paid')->sum('amount');
    }

    public function getBillDueAttribute()
    {
        return $this->bill_total - $this->paid_total;
    }

    // ---- customer side (what customers owe for items in this shipment) ----
    public function getItemsTotalAttribute()
    {
        return (float) $this->items()->sum('final_price');
    }

    public function getReceivedTotalAttribute()
    {
        return (float) $this->payments()->where('type', 'received')->sum('amount');
    }

    public function getCollectionDueAttribute()
    {
        return $this->items_total - $this->received_total;
    }
}
