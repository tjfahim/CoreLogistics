<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdParty extends Model
{
    protected $fillable = ['name', 'code', 'contact_person', 'phone', 'email', 'address', 'note', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function items()    { return $this->hasMany(CargoItem::class); }
    public function payments() { return $this->hasMany(ThirdPartyPayment::class); }

    /** Colour used for this company's badge everywhere (stable per id). */
    public function getColorAttribute(): string
    {
        $palette = ['#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#0d6efd', '#d63384', '#198754', '#6610f2', '#dc3545', '#0dcaf0'];
        return $palette[$this->id % count($palette)];
    }
}
