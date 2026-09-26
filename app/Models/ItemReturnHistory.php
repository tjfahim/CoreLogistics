<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReturnHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'cargo_item_id',
        'items_shipment_id',
        'note',
        'return_date',
        'previous_status',
        'returned_by',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function cargoItem()
    {
        return $this->belongsTo(CargoItem::class);
    }

    public function shipment()
    {
        return $this->belongsTo(ItemsShipment::class, 'items_shipment_id');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}