<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyPayment extends Model
{
    protected $guarded = [];
    protected $casts = ['payment_date' => 'date', 'amount' => 'float'];
}
