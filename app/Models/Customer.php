<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'customer_code',
        'shipping_mark1',
        'shipping_mark2',
        'shipping_mark3',
        'shipping_mark4',
        'address',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function cargoItems()
    {
        return $this->hasMany(CargoItem::class);
    }

    // Extra shipping marks relationship
    public function extraShippingMarks()
    {
        return $this->hasMany(CustomerExtraShippingMark::class);
    }

    // Get all shipping marks (4 fixed + unlimited extras)
    public function getAllShippingMarksAttribute()
    {
        $marks = array_filter([
            $this->shipping_mark1,
            $this->shipping_mark2,
            $this->shipping_mark3,
            $this->shipping_mark4,
        ], function ($m) {
            return !empty(trim($m ?? ''));
        });

        $extra = $this->extraShippingMarks->pluck('shipping_mark')->toArray();

        return array_values(array_unique(array_merge($marks, $extra)));
    }

    public function getFullAddressAttribute()
    {
        return $this->address;
    }

    public function scopeActive($query)
    {
        return $query->whereHas('cargoItems');
    }

    public static function generateCustomerCode()
    {
        $latest = self::latest()->first();
        $number = $latest ? intval(substr($latest->customer_code, 3)) + 1 : 1;
        return 'CUS' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalBillAttribute()
    {
        return $this->cargoItems()
            ->where(function ($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->sum(DB::raw('COALESCE(price, price_per_kg * weight_kg)'));
    }


public function payments()
{
    return $this->hasMany(CustomerPayment::class);
}

public function allocations()
{
    return $this->hasManyThrough(CustomerPaymentAllocation::class, CustomerPayment::class, 'customer_id', 'customer_payment_id');
}

public function getTotalPaidAttribute()
{
    return (float) $this->allocations()->sum('customer_payment_allocations.amount');
}
    public function getTotalDueAttribute()
    {
        return $this->total_bill - $this->total_paid;
    }

    public function getUnpaidItems()
    {
        return $this->cargoItems()
            ->where(function ($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->whereNull('paid_at')
            ->get();
    }

    public function getPaidItems()
    {
        return $this->cargoItems()
            ->where(function ($query) {
                $query->whereNotNull('price')
                      ->orWhereNotNull('price_per_kg');
            })
            ->whereNotNull('paid_at')
            ->get();
    }
}