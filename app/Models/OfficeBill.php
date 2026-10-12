<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeBill extends Model
{
    protected $fillable = ['office_id', 'year', 'month', 'amount', 'note'];

    public function office() { return $this->belongsTo(Office::class); }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }
}