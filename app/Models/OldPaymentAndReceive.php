<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldPaymentAndReceive extends Model
{
    protected $table = 'old_payment_and_receives';

    protected $fillable = [
        'type', 'old_shipment_id', 'old_customer_id', 'amount', 'date', 'method', 'note',
    ];

    protected $casts = ['date' => 'date'];

    public function shipment()
    {
        return $this->belongsTo(OldShipment::class, 'old_shipment_id');
    }

    public function customer()
    {
        return $this->belongsTo(OldCustomer::class, 'old_customer_id');
    }
}
