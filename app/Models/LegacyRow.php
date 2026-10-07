<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyRow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'delivered_at' => 'date',
        'issues'       => 'array',
        'weight_kg'    => 'float',
        'price'        => 'float',
        'paid_amount'  => 'float',
    ];

    public function upload() { return $this->belongsTo(LegacyUpload::class, 'upload_id'); }

    public function getBalanceAttribute()
    {
        return round($this->price - $this->paid_amount, 2);
    }
}
