<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldShipmentItem extends Model
{
    protected $fillable = [
        'old_shipment_id', 'old_customer_id', 'name', 'weight', 'ctn_no',
        'cbm', 'remark', 'shipping_mark', 'price_per_kg', 'final_price',
    ];

    public function shipment()
    {
        return $this->belongsTo(OldShipment::class, 'old_shipment_id');
    }

    public function customer()
    {
        return $this->belongsTo(OldCustomer::class, 'old_customer_id');
    }
}
