<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    // A payment is now a RECEIPT (total money received).
    // How it is split between shipments lives in allocations().
    protected $fillable = [
        'customer_id',
        'user_id',           // system user who received / saved the payment
        'itemshipment_id',   // only filled when the receipt went to exactly one shipment (legacy / convenience)
        'amount',
        'payment_method',
        'payment_date',
        'reference_no',
        'notes',
        'remaining_due'      // customer's total due right after this payment
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itemshipment()
    {
        return $this->belongsTo(ItemsShipment::class);
    }

    // NEW: split of this receipt across shipments
    public function allocations()
    {
        return $this->hasMany(CustomerPaymentAllocation::class, 'customer_payment_id');
    }

    public function items()
    {
        return $this->belongsToMany(CargoItem::class, 'customer_payment_items')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    // Get payment items count (legacy item-level payments)
    public function getItemCountAttribute()
    {
        return $this->items()->count();
    }

    // Get payment items list (legacy item-level payments)
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