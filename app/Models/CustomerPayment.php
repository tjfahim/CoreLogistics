<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'itemshipment_id', 
        'amount',
        'payment_method',
        'payment_date',
        'reference_no',
        'notes',
        'remaining_due'
    ];
    
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
     public function itemshipment()
    {
        return $this->belongsTo(ItemsShipment::class);
    }
    public function items()
    {
        return $this->belongsToMany(CargoItem::class, 'customer_payment_items')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
    
    // Get payment items count
    public function getItemCountAttribute()
    {
        return $this->items()->count();
    }
    
    // Get payment items list
    public function getItemsListAttribute()
    {
        return $this->items->map(function($item) {
            return $item->goods_name_en . ' - Tk' . number_format($item->pivot->amount, 2);
        })->implode(', ');
    }
     public function paymentItems()
    {
        return $this->hasMany(CustomerPaymentItem::class);
    }
     public function cargoItems()
    {
        return $this->belongsToMany(CargoItem::class, 'customer_payment_items', 'customer_payment_id', 'cargo_item_id')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

}
