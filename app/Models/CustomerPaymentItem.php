<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_payment_id',
        'cargo_item_id',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(CustomerPayment::class);
    }

    public function cargoItem()
    {
        return $this->belongsTo(CargoItem::class);
    }
}