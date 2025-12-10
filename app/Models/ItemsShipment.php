<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsShipment extends Model
{
    use HasFactory;
     protected $fillable = [
        'shipment_name',
        'shipment_date',
        'note',
        'status'
    ];

    protected $casts = [
        'shipment_date' => 'date',
    ];

     public function cargoItems()
    {
        return $this->belongsToMany(CargoItem::class, 'cargo_item_shipment');
    }

    public function costs()
    {
        return $this->hasMany(ItemsShipmentCost::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(ItemsShipmentPayment::class, ItemsShipmentCost::class);
    }

    // Calculate total cost attributes
    public function getTotalCostAttribute()
    {
        return $this->costs->sum('amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->costs->sum('paid_amount');
    }

    public function getTotalDueAttribute()
    {
        return $this->costs->sum('due_amount');
    }

    // Calculate shipment statistics
    public function getTotalWeightAttribute()
    {
        return $this->cargoItems->sum('weight_kg');
    }

    public function getTotalCbmAttribute()
    {
        return $this->cargoItems->sum('cbm');
    }

    public function getTotalItemsAttribute()
    {
        return $this->cargoItems->count();
    }

    // Cost statistics by status
    public function getPendingCostsAttribute()
    {
        return $this->costs->where('status', 'pending')->sum('amount');
    }

    public function getPartialCostsAttribute()
    {
        return $this->costs->where('status', 'partial')->sum('amount');
    }

    public function getPaidCostsAttribute()
    {
        return $this->costs->where('status', 'paid')->sum('amount');
    }

    public static function generateShipmentName()
    {
        $latestShipment = self::latest()->first();
        $nextNumber = $latestShipment ? (intval(substr($latestShipment->shipment_name, 5)) + 1) : 1;
        return 'Draft' . $nextNumber;
    }
    public function billing()
{
    return $this->hasMany(CargoItem::class, 'shipment_id')->with('customer');
}
}
