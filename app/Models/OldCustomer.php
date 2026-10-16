<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldCustomer extends Model
{
    protected $fillable = ['name', 'number'];

    public function items()
    {
        return $this->hasMany(OldShipmentItem::class);
    }

    public function payments()
    {
        return $this->hasMany(OldPaymentAndReceive::class);
    }

    /** Shipments this customer has items in. */
    public function shipments()
    {
        return OldShipment::whereIn(
            'id',
            $this->items()->pluck('old_shipment_id')
        )->orderBy('date', 'desc')->get();
    }

    public function getBillTotalAttribute()
    {
        return (float) $this->items()->sum('final_price');
    }

    public function getReceivedTotalAttribute()
    {
        return (float) $this->payments()->where('type', 'received')->sum('amount');
    }

    public function getDueAttribute()
    {
        return $this->bill_total - $this->received_total;
    }
}
