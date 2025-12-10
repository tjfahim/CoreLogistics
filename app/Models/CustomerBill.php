<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBill extends Model
{
    use HasFactory;
    protected $fillable = [
        'shipment_id',
        'customer_id',
        'total_weight',
        'total_price',
        'paid_amount',
        'due_amount',
        'status',
    ];

    public function deliveries()
    {
        return $this->hasMany(CustomerDelivery::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
