<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDelivery extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_bill_id',
        'delivered_weight',
        'delivery_date',
        'amount',
        'notes'
    ];

    public function bill()
    {
        return $this->belongsTo(CustomerBill::class, 'customer_bill_id');
    }
}
