<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentPayment extends Model
{
    use HasFactory;
       protected $fillable = [
        'shipment_cost_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
        'paid_by'
    ];

    public function shipmentCost()
    {
        return $this->belongsTo(ShipmentCost::class);
    }
}
