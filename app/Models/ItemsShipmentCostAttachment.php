<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ItemsShipmentCostAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'items_shipment_cost_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function cost()
    {
        return $this->belongsTo(ItemsShipmentCost::class, 'items_shipment_cost_id');
    }

    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getIsImageAttribute()
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getSizeKbAttribute()
    {
        return round($this->file_size / 1024, 1);
    }
}