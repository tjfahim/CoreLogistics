<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeBill extends Model
{
    protected $fillable = ['office_id', 'bill_type', 'bill_date', 'year', 'month', 'amount', 'note'];

    protected $casts = ['bill_date' => 'date'];

    public function office() { return $this->belongsTo(Office::class); }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }
}