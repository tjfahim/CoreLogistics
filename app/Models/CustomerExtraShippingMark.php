<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerExtraShippingMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shipping_mark',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Scope: search by customer name or shipping mark
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('shipping_mark', 'LIKE', "%{$term}%")
              ->orWhereHas('customer', function ($cq) use ($term) {
                  $cq->where('name', 'LIKE', "%{$term}%")
                     ->orWhere('customer_code', 'LIKE', "%{$term}%");
              });
        });
    }
}