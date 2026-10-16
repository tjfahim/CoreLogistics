<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldShipmentBill extends Model
{
    protected $fillable = ['old_shipment_id', 'cost_type', 'total_price'];

    public function shipment()
    {
        return $this->belongsTo(OldShipment::class, 'old_shipment_id');
    }
}
