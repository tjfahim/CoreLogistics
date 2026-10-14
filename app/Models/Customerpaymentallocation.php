<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_payment_id',
        'itemshipment_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(CustomerPayment::class, 'customer_payment_id');
    }

    public function itemshipment()
    {
        return $this->belongsTo(ItemsShipment::class, 'itemshipment_id');
    }
}