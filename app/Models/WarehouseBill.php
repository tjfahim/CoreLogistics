<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseBill extends Model
{
    protected $fillable = [
        'office_id', 'kg', 'rate_per_kg', 'total_amount', 'bill_date', 'note',
    ];

    protected $casts = ['bill_date' => 'date'];

    public function office() { return $this->belongsTo(Office::class); }
}