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

     public function getFullAddressAttribute()
    {
        return $this->address;
    }

    // Scope for active customers
    public function scopeActive($query)
    {
        return $query->whereHas('cargoItems');
    }

    // Generate customer code automatically
    public static function generateCustomerCode()
    {
        $latest = self::latest()->first();
        $number = $latest ? intval(substr($latest->customer_code, 3)) + 1 : 1;
        return 'CUS' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalBillAttribute()
{
    return $this->cargoItems()
        ->where(function($query) {
            $query->whereNotNull('price')
                  ->orWhereNotNull('price_per_kg');
        })
        ->sum(DB::raw('COALESCE(price, price_per_kg * weight_kg)'));
}

public function getTotalPaidAttribute()
{
    return $this->cargoItems()
        ->where(function($query) {
            $query->whereNotNull('price')
                  ->orWhereNotNull('price_per_kg');
        })
        ->whereNotNull('paid_at')
        ->sum(DB::raw('COALESCE(price, price_per_kg * weight_kg)'));
}

public function getTotalDueAttribute()
{
    return $this->total_bill - $this->total_paid;
}

public function getUnpaidItems()
{
    return $this->cargoItems()
        ->where(function($query) {
            $query->whereNotNull('price')
                  ->orWhereNotNull('price_per_kg');
        })
        ->whereNull('paid_at')
        ->get();
}

public function getPaidItems()
{
    return $this->cargoItems()
        ->where(function($query) {
            $query->whereNotNull('price')
                  ->orWhereNotNull('price_per_kg');
        })
        ->whereNotNull('paid_at')
        ->get();
}
}
