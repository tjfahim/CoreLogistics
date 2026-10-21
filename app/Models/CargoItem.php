<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoItem extends Model
{
    use HasFactory;

    /** Warehouse -> delivery flow, in order. key = cargo_items.status, value = label. */
    public const STATUS_FLOW = [
        'pending'             => 'Received (China)',
        'packed'              => 'Packed',
        'ready_to_ship'       => 'Ready to Ship',
        'shipping_ongoing'    => 'Shipping Ongoing',
        'arrived_bd'          => 'Arrived in BD',
        'waiting_for_customs' => 'Waiting for Customs',
        'ready_to_deliver'    => 'Ready to Deliver',
        'delivered'           => 'Delivered',
    ];

    protected $fillable = [
        'goods_received',
        'receipt_no',
        'printed_ctn_no',
        'written_ctn_no',
        'tracking_no',
        'goods_name_en',
        'goods_name_local',
        'pack_no',
        'unit_pcs',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'cbm',
        'shipping_mark',
        'remark',
        'is_copy',
        'warehouse',
        'date',
        'status',
        'price',
        'price_per_kg',
        'customer_id',
        'box_no',
        'third_party_id',
        'cancelled_at',
        'cancel_reason',
        'status_before_cancel',
    ];

    protected static function booted(): void
    {
        // keep a dated trail of status changes (feeds the daily / date-range reports)
        static::updated(function (CargoItem $item) {
            if ($item->wasChanged('status')) {
                CargoItemStatusLog::recordMany([$item->id], $item->status);
            }
        });
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParty::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(CargoItemStatusLog::class);
    }

    protected $casts = [
        'date'    => 'date',
        'cancelled_at' => 'datetime',
        'is_copy' => 'boolean',
    ];

     public function itemshipments()
    {
        return $this->belongsToMany(ItemsShipment::class, 'cargo_item_shipment');
    }

      public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for packed items
    public function scopePacked($query)
    {
        return $query->where('status', 'packed');
    }
public function returnHistories()
{
    return $this->hasMany(ItemReturnHistory::class)->latest('return_date');
}

public function latestReturn()
{
    return $this->hasOne(ItemReturnHistory::class)->latestOfMany('return_date');
}
  
      public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
     
    public function scopeAvailableForShipment($query)
    {
        return $query->whereIn('status', ['pending', 'packed']);
    }

    public function scopeWithoutCustomer($query)
    {
        return $query->whereNull('customer_id');
    }

    public function scopeWithCustomer($query)
    {
        return $query->whereNotNull('customer_id');
    }
       public function getTotalPriceAttribute()
    {
        if ($this->price) {
            return $this->price;
        } elseif ($this->price_per_kg && $this->weight_kg) {
            return $this->price_per_kg * $this->weight_kg;
        }
        return null;
    }

    public function hasPrice()
    {
        return !is_null($this->price) || !is_null($this->price_per_kg);
    }
    // Add to CargoItem model
public function itemPayments()
{
    return $this->hasMany(CargoItemPayment::class);
}

public function getPaidAmountAttribute()
{
    return $this->itemPayments->sum('amount_paid');
}

public function getRemainingAmountAttribute()
{
    return max(0, $this->total_price - $this->paid_amount);
}

public function getIsFullyPaidAttribute()
{
    return $this->remaining_amount <= 0;
}

public function getPaymentPercentageAttribute()
{
    if ($this->total_price <= 0) return 0;
    return round(($this->paid_amount / $this->total_price) * 100, 2);
}
public function paymentItems()
{
    return $this->hasMany(CustomerPaymentItem::class);
}

public function payments()
{
    return $this->belongsToMany(CustomerPayment::class, 'customer_payment_items', 'cargo_item_id', 'customer_payment_id')
                ->withPivot('amount')
                ->withTimestamps();
}
}
